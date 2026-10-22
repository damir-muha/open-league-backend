<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Season;
use App\Models\Standing;
use App\Models\Group;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class StandingCalculationService
{
    /**
     * Пересчитывает турнирную таблицу с нуля для всего сезона.
     * Сохраняет как общую таблицу, так и слепки по турам.
     */
    public function recalculateForSeason(Season $season): void
    {
        DB::transaction(function () use ($season) {
            // 1. Удаляем старые слепки по турам (где round_id не null)
            Standing::where('season_id', $season->id)->whereNotNull('round_id')->delete();

            // 2. Получаем все матчи сезона, отсортированные хронологически
            $games = Game::where('season_id', $season->id)
                ->where('status', 'finished')
                ->whereHas('stage', function ($q) {
                    $q->where('type', '!=', 'knockout'); // Игнорируем плей-офф
                })
                ->orderBy('game_datetime', 'asc')
                ->get();

            // 3. Сохраняем штрафы перед очисткой таблицы
            // Штрафы хранятся с привязкой к конкретной группе или общие (group_id = null)
            $oldStandings = Standing::where('season_id', $season->id)->whereNull('round_id')->get();
            $penalties = [];
            foreach ($oldStandings as $os) {
                if ($os->points_deducted > 0) {
                    $groupId = $os->group_id ?? 0;
                    $penalties[$groupId][$os->team_id] = $os->points_deducted;
                }
            }

            // 4. Очищаем все существующие общие таблицы для этого сезона
            Standing::where('season_id', $season->id)->whereNull('round_id')->delete();

            // Инициализируем массив для хранения статистики в памяти
            $stats = []; // Формат: $stats[group_id][team_id] = [...]

            // 5. Инициализируем команды во ВСЕХ их группах (одна команда может быть в нескольких группах, например в предварительном раунде и финальном)
            $groups = Group::whereHas('stage', function ($q) use ($season) {
                $q->where('season_id', $season->id);
            })->with('teams')->get();

            foreach ($groups as $group) {
                foreach ($group->teams as $team) {
                    $stats[$group->id][$team->id] = $this->getEmptyStatArray($team->id);
                }
            }

            // Также инициализируем команды, которые просто привязаны к сезону (group_id = null -> 0)
            $seasonTeams = $season->teams()->get();
            foreach ($seasonTeams as $team) {
                // Если команда вообще ни в одной группе не состоит, создаем для нее общую запись
                $isInAnyGroup = false;
                foreach ($groups as $group) {
                    if ($group->teams->contains('id', $team->id)) {
                        $isInAnyGroup = true;
                        break;
                    }
                }
                if (!$isInAnyGroup || count($groups) === 0) {
                    $stats[0][$team->id] = $this->getEmptyStatArray($team->id);
                }
            }
            
            // На всякий случай добавляем команды, которые почему-то были в старых таблицах, но не привязаны ни к сезону, ни к группам
            // Удалено, так как это вызывало баг с дублированием команд (восстанавливались сломанные записи из старых расчетов).
            // Теперь источником истины является только `$season->teams` и `group_team`.

            $groupsConfig = $groups->keyBy('id');

            $rankingRules = $season->ranking_rules ?? [
                ['column' => 'points', 'direction' => 'desc'],
                ['column' => 'goal_difference', 'direction' => 'desc']
            ];

            if ($games->isEmpty()) {
                $this->sortAndSaveSnapshots($season, $stats, $rankingRules, $penalties, null, $groupsConfig);
                $this->clearCache($season);
                return;
            }

            // Группируем игры по турам, чтобы делать слепки
            $gamesByRound = [];
            foreach ($games as $game) {
                $roundKey = $game->round_id ?? 'no_round';
                $gamesByRound[$roundKey][] = $game;
            }

            // Проходим по каждому туру и обновляем статистику
            foreach ($gamesByRound as $roundId => $roundGames) {
                foreach ($roundGames as $game) {
                    $this->applyGameToStats($stats, $game);
                }

                // Сортируем и сохраняем слепок тура (если round_id существует)
                if ($roundId !== 'no_round') {
                    $this->sortAndSaveSnapshots($season, $stats, $rankingRules, $penalties, $roundId, $groupsConfig);
                }
            }

            // Сохраняем финальную (общую) таблицу
            $this->sortAndSaveSnapshots($season, $stats, $rankingRules, $penalties, null, $groupsConfig);
            
            // Очищаем кэш API, чтобы изменения появились моментально
            $this->clearCache($season);
        });
    }

    protected function applyGameToStats(array &$stats, Game $game)
    {
        $homeId = $game->home_team_id;
        $awayId = $game->away_team_id;
        $homeScore = (int) $game->home_score;
        $awayScore = (int) $game->away_score;

        // Берем группу СТРОГО из матча!
        // Это важно, так как команда может играть матчи в разных группах (например, первый этап и второй этап).
        $groupId = $game->group_id ?? 0;

        if (!isset($stats[$groupId][$homeId])) {
            $stats[$groupId][$homeId] = $this->getEmptyStatArray($homeId);
        }
        if (!isset($stats[$groupId][$awayId])) {
            $stats[$groupId][$awayId] = $this->getEmptyStatArray($awayId);
        }

        // Общая статистика
        $stats[$groupId][$homeId]['played']++;
        $stats[$groupId][$awayId]['played']++;
        
        $stats[$groupId][$homeId]['goals_for'] += $homeScore;
        $stats[$groupId][$homeId]['goals_against'] += $awayScore;
        $stats[$groupId][$homeId]['goal_difference'] = $stats[$groupId][$homeId]['goals_for'] - $stats[$groupId][$homeId]['goals_against'];

        $stats[$groupId][$awayId]['goals_for'] += $awayScore;
        $stats[$groupId][$awayId]['goals_against'] += $homeScore;
        $stats[$groupId][$awayId]['goal_difference'] = $stats[$groupId][$awayId]['goals_for'] - $stats[$groupId][$awayId]['goals_against'];

        // Домашняя/Гостевая статистика
        $stats[$groupId][$homeId]['home_played']++;
        $stats[$groupId][$homeId]['home_goals_for'] += $homeScore;
        $stats[$groupId][$homeId]['home_goals_against'] += $awayScore;

        $stats[$groupId][$awayId]['away_played']++;
        $stats[$groupId][$awayId]['away_goals_for'] += $awayScore;
        $stats[$groupId][$awayId]['away_goals_against'] += $homeScore;

        // Начисление очков и побед
        if ($homeScore > $awayScore) {
            $stats[$groupId][$homeId]['wins']++;
            $stats[$groupId][$homeId]['points'] += 3;
            $stats[$groupId][$homeId]['home_wins']++;
            $stats[$groupId][$homeId]['home_points'] += 3;
            
            $stats[$groupId][$awayId]['losses']++;
            $stats[$groupId][$awayId]['away_losses']++;
            
            $this->addForm($stats[$groupId][$homeId]['recent_form'], 'W');
            $this->addForm($stats[$groupId][$awayId]['recent_form'], 'L');
        } elseif ($homeScore < $awayScore) {
            $stats[$groupId][$awayId]['wins']++;
            $stats[$groupId][$awayId]['points'] += 3;
            $stats[$groupId][$awayId]['away_wins']++;
            $stats[$groupId][$awayId]['away_points'] += 3;
            
            $stats[$groupId][$homeId]['losses']++;
            $stats[$groupId][$homeId]['home_losses']++;
            
            $this->addForm($stats[$groupId][$homeId]['recent_form'], 'L');
            $this->addForm($stats[$groupId][$awayId]['recent_form'], 'W');
        } else {
            $stats[$groupId][$homeId]['draws']++;
            $stats[$groupId][$homeId]['points'] += 1;
            $stats[$groupId][$homeId]['home_draws']++;
            $stats[$groupId][$homeId]['home_points'] += 1;
            
            $stats[$groupId][$awayId]['draws']++;
            $stats[$groupId][$awayId]['points'] += 1;
            $stats[$groupId][$awayId]['away_draws']++;
            $stats[$groupId][$awayId]['away_points'] += 1;
            
            $this->addForm($stats[$groupId][$homeId]['recent_form'], 'D');
            $this->addForm($stats[$groupId][$awayId]['recent_form'], 'D');
        }

        // Сохраняем информацию о матче для расчета личных встреч внутри этой группы
        $stats[$groupId][$homeId]['matches'][] = ['opponent' => $awayId, 'scored' => $homeScore, 'conceded' => $awayScore];
        $stats[$groupId][$awayId]['matches'][] = ['opponent' => $homeId, 'scored' => $awayScore, 'conceded' => $homeScore];
    }

    protected function addForm(array &$form, string $result)
    {
        array_push($form, $result);
        if (count($form) > 5) {
            array_shift($form); // Оставляем только последние 5
        }
    }

    protected function getEmptyStatArray($teamId): array
    {
        return [
            'team_id' => $teamId,
            'played' => 0, 'wins' => 0, 'draws' => 0, 'losses' => 0, 'goals_for' => 0, 'goals_against' => 0, 'goal_difference' => 0, 'points' => 0,
            'home_played' => 0, 'home_wins' => 0, 'home_draws' => 0, 'home_losses' => 0, 'home_goals_for' => 0, 'home_goals_against' => 0, 'home_points' => 0,
            'away_played' => 0, 'away_wins' => 0, 'away_draws' => 0, 'away_losses' => 0, 'away_goals_for' => 0, 'away_goals_against' => 0, 'away_points' => 0,
            'recent_form' => [],
            'matches' => [],
        ];
    }

    protected function sortAndSaveSnapshots(Season $season, array $statsByGroup, array $rankingRules, array $penalties, ?int $roundId, $groupsConfig)
    {
        foreach ($statsByGroup as $groupId => $teamsStats) {
            
            $groupConfig = $groupsConfig->get($groupId);
            $parentGroupId = $groupConfig ? $groupConfig->inherits_from_group_id : null;
            $inheritMode = $groupConfig ? $groupConfig->inherit_mode : 'none';
            
            $teamsStatsForSaving = [];

            // Применяем штрафные очки (points_deducted) и наследование
            foreach ($teamsStats as $teamId => $stat) {
                $statForSaving = $stat; // Копия, чтобы не менять оригинальный массив (он нужен для следующих туров)
                
                if ($parentGroupId && $inheritMode !== 'none' && isset($statsByGroup[$parentGroupId][$teamId])) {
                    $parentStat = $statsByGroup[$parentGroupId][$teamId];
                    
                    // Переносим всю числовую статистику
                    $fields = [
                        'played', 'wins', 'draws', 'losses', 'goals_for', 'goals_against', 'goal_difference', 
                        'home_played', 'home_wins', 'home_draws', 'home_losses', 'home_goals_for', 'home_goals_against',
                        'away_played', 'away_wins', 'away_draws', 'away_losses', 'away_goals_for', 'away_goals_against'
                    ];
                    
                    foreach ($fields as $f) {
                        $statForSaving[$f] += $parentStat[$f];
                    }
                    
                    // Переносим очки с учетом правила деления
                    if ($inheritMode === 'full') {
                        $statForSaving['points'] += $parentStat['points'];
                        $statForSaving['home_points'] += $parentStat['home_points'];
                        $statForSaving['away_points'] += $parentStat['away_points'];
                    } elseif ($inheritMode === 'halved_up' || $inheritMode === 'halved_down') {
                        $func = ($inheritMode === 'halved_up') ? 'ceil' : 'floor';
                        $statForSaving['points'] += $func($parentStat['points'] / 2);
                        $statForSaving['home_points'] += $func($parentStat['home_points'] / 2);
                        $statForSaving['away_points'] += $func($parentStat['away_points'] / 2);
                    }
                    
                    // Переносим форму и историю личных встреч
                    $mergedForm = array_merge($parentStat['recent_form'], $statForSaving['recent_form']);
                    $statForSaving['recent_form'] = array_slice($mergedForm, -5);
                    $statForSaving['matches'] = array_merge($parentStat['matches'], $statForSaving['matches']);
                }

                $penalty = $penalties[$groupId][$teamId] ?? ($penalties[0][$teamId] ?? 0);
                $statForSaving['final_points'] = $statForSaving['points'] - $penalty;
                $statForSaving['points_deducted'] = $penalty;
                
                $teamsStatsForSaving[] = $statForSaving;
            }

            // Сортируем команды
            usort($teamsStatsForSaving, function ($a, $b) use ($rankingRules) {
                foreach ($rankingRules as $rule) {
                    $column = $rule['column'] ?? 'points';
                    $direction = $rule['direction'] ?? 'desc';
                    $mult = strtolower($direction) === 'asc' ? -1 : 1; 

                    if ($column === 'points') {
                        $column = 'final_points'; // Используем очки с учетом штрафов
                    }

                    if ($column === 'head_to_head') {
                        $h2hResult = $this->calculateHeadToHead($a, $b);
                        if ($h2hResult !== 0) {
                            return $h2hResult * -$mult;
                        }
                        continue;
                    }

                    $valA = $a[$column] ?? 0;
                    $valB = $b[$column] ?? 0;

                    if ($valA !== $valB) {
                        return ($valA <=> $valB) * -$mult;
                    }
                }
                return 0; // Полное равенство
            });

            // Сохраняем в БД
            $position = 1;
            $insertData = [];
            foreach ($teamsStatsForSaving as $stat) {
                $standingData = [
                    'season_id' => $season->id,
                    'team_id' => $stat['team_id'],
                    'group_id' => $groupId === 0 ? null : $groupId,
                    'round_id' => $roundId,
                    'position' => $position++,
                    'played' => $stat['played'],
                    'wins' => $stat['wins'],
                    'draws' => $stat['draws'],
                    'losses' => $stat['losses'],
                    'goals_for' => $stat['goals_for'],
                    'goals_against' => $stat['goals_against'],
                    'goal_difference' => $stat['goal_difference'],
                    'points' => $stat['points'], 
                    'points_deducted' => $stat['points_deducted'],
                    
                    'home_played' => $stat['home_played'],
                    'home_wins' => $stat['home_wins'],
                    'home_draws' => $stat['home_draws'],
                    'home_losses' => $stat['home_losses'],
                    'home_goals_for' => $stat['home_goals_for'],
                    'home_goals_against' => $stat['home_goals_against'],
                    'home_points' => $stat['home_points'],
                    
                    'away_played' => $stat['away_played'],
                    'away_wins' => $stat['away_wins'],
                    'away_draws' => $stat['away_draws'],
                    'away_losses' => $stat['away_losses'],
                    'away_goals_for' => $stat['away_goals_for'],
                    'away_goals_against' => $stat['away_goals_against'],
                    'away_points' => $stat['away_points'],
                    
                    'recent_form' => json_encode($stat['recent_form']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                if ($roundId === null) {
                    // Используем updateOrCreate для избежания гонок (race conditions) при дублировании
                    Standing::updateOrCreate([
                        'season_id' => $season->id,
                        'team_id' => $stat['team_id'],
                        'group_id' => $groupId === 0 ? null : $groupId,
                        'round_id' => null,
                    ], $standingData);
                } else {
                    $insertData[] = $standingData;
                }
            }
            
            if (!empty($insertData)) {
                Standing::insert($insertData);
            }
        }
    }

    /**
     * Вычисляет результат личных встреч между двумя командами.
     */
    protected function calculateHeadToHead(array $a, array $b): int
    {
        $aPoints = 0;
        $bPoints = 0;
        $aGoalsDiff = 0;
        $bGoalsDiff = 0;

        foreach ($a['matches'] as $match) {
            if ($match['opponent'] === $b['team_id']) {
                $aGoalsDiff += ($match['scored'] - $match['conceded']);
                $bGoalsDiff += ($match['conceded'] - $match['scored']);

                if ($match['scored'] > $match['conceded']) {
                    $aPoints += 3;
                } elseif ($match['scored'] < $match['conceded']) {
                    $bPoints += 3;
                } else {
                    $aPoints += 1;
                    $bPoints += 1;
                }
            }
        }

        if ($aPoints !== $bPoints) {
            return $aPoints <=> $bPoints;
        }

        if ($aGoalsDiff !== $bGoalsDiff) {
            return $aGoalsDiff <=> $bGoalsDiff;
        }

        return 0; 
    }
    
    protected function clearCache(Season $season)
    {
        Cache::forget('api_season_standings_' . $season->id . '_round_all');
        // Очищаем кэш для всех возможных раундов (если они запрашивались)
        $rounds = Game::where('season_id', $season->id)->whereNotNull('round_id')->distinct('round_id')->pluck('round_id');
        foreach ($rounds as $roundId) {
            Cache::forget('api_season_standings_' . $season->id . '_round_' . $roundId);
        }
    }
}