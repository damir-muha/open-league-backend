<?php

namespace App\Filament\Resources\PlayerResource\Pages;

use App\Filament\Resources\PlayerResource;
use Filament\Resources\Pages\Page;
use App\Models\Player;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class ManageDuplicatePlayers extends Page
{
    protected static string $resource = PlayerResource::class;
    
    protected static ?string $title = 'Manage Duplicate Players';

    protected static string $view = 'filament.resources.player-resource.pages.manage-duplicate-players';

    public array $duplicateGroups = [];
    public array $selectedPrimaryIds = [];

    public function mount()
    {
        $this->loadDuplicates();
    }

    public function loadDuplicates()
    {
        $players = Player::whereNotNull('birth_date')->get();

        $grouped = $players->groupBy(function ($p) {
            $nFirst = Player::normalizeKazakhName($p->first_name);
            $nLast = Player::normalizeKazakhName($p->last_name);
            
            $bDate = $p->birth_date instanceof \Carbon\Carbon 
                ? $p->birth_date->format('Y-m-d') 
                : (is_string($p->birth_date) ? date('Y-m-d', strtotime($p->birth_date)) : 'unknown');
                
            return $nFirst . '|' . $nLast . '|' . $bDate;
        });

        $this->duplicateGroups = $grouped->filter(fn($group) => $group->count() > 1)
            ->map(function ($group) {
                return $group->values()->map(function ($player) {
                    $createdAt = '';
                    if ($player->created_at instanceof \Carbon\Carbon) {
                        $createdAt = $player->created_at->format('Y-m-d H:i');
                    } elseif (is_string($player->created_at)) {
                        $createdAt = $player->created_at;
                    }

                    $birthDate = '';
                    if ($player->birth_date instanceof \Carbon\Carbon) {
                        $birthDate = $player->birth_date->format('Y-m-d');
                    } elseif (is_string($player->birth_date)) {
                        $birthDate = $player->birth_date;
                    }

                    return [
                        'id' => (int) $player->id,
                        'first_name' => mb_convert_encoding((string) $player->first_name, 'UTF-8', 'UTF-8'),
                        'last_name' => mb_convert_encoding((string) $player->last_name, 'UTF-8', 'UTF-8'),
                        'birth_date' => $birthDate,
                        'registration_id' => (string) $player->registration_id,
                        'created_at' => $createdAt,
                    ];
                })->toArray();
            })
            ->values()
            ->toArray();

        // Auto-select the first player of each group as the primary
        $this->selectedPrimaryIds = [];
        foreach ($this->duplicateGroups as $index => $group) {
            if (isset($group[0]['id'])) {
                $this->selectedPrimaryIds[$index] = $group[0]['id'];
            }
        }
    }

    public function mergeAll()
    {
        if (empty($this->duplicateGroups)) {
            return;
        }

        DB::beginTransaction();
        try {
            $mergedCount = 0;

            foreach ($this->duplicateGroups as $index => $group) {
                $primaryId = $this->selectedPrimaryIds[$index] ?? null;
                if (!$primaryId) continue;

                $primary = Player::find($primaryId);
                if (!$primary) continue;

                $duplicateIds = array_column($group, 'id');

                foreach ($duplicateIds as $dupId) {
                    if ($dupId == $primaryId) continue;
                    
                    $duplicate = Player::find($dupId);
                    if (!$duplicate) continue;

                    // 1. Events
                    DB::table('events')->where('player_id', $duplicate->id)->update(['player_id' => $primary->id]);
                    DB::table('events')->where('related_player_id', $duplicate->id)->update(['related_player_id' => $primary->id]);

                    // 2. Suspensions
                    DB::table('suspensions')->where('player_id', $duplicate->id)->update(['player_id' => $primary->id]);

                    // 3. Transfer Requests
                    DB::table('transfer_requests')->where('player_id', $duplicate->id)->update(['player_id' => $primary->id]);

                    // 4. Player Season Roster
                    $rosters = DB::table('player_season_roster')->where('player_id', $duplicate->id)->get();
                    foreach ($rosters as $roster) {
                        $exists = DB::table('player_season_roster')
                            ->where('player_id', $primary->id)
                            ->where('season_id', $roster->season_id)
                            ->exists();

                        if (!$exists) {
                            DB::table('player_season_roster')
                                ->where('player_id', $duplicate->id)
                                ->where('season_id', $roster->season_id)
                                ->update(['player_id' => $primary->id]);
                        } else {
                            DB::table('player_season_roster')
                                ->where('player_id', $duplicate->id)
                                ->where('season_id', $roster->season_id)
                                ->delete();
                        }
                    }

                    // 5. Game Lineups
                    DB::table('game_lineups')->where('player_id', $duplicate->id)->update(['player_id' => $primary->id]);

                    // 6. Player Season Stats
                    $stats = DB::table('player_season_stats')->where('player_id', $duplicate->id)->get();
                    foreach ($stats as $stat) {
                        $primaryStat = DB::table('player_season_stats')
                            ->where('player_id', $primary->id)
                            ->where('season_id', $stat->season_id)
                            ->where('team_id', $stat->team_id)
                            ->first();

                        if ($primaryStat) {
                            // Sum the stats if primary already has stats for this season and team
                            DB::table('player_season_stats')
                                ->where('id', $primaryStat->id)
                                ->update([
                                    'games_played' => $primaryStat->games_played + $stat->games_played,
                                    'goals' => $primaryStat->goals + $stat->goals,
                                    'assists' => $primaryStat->assists + $stat->assists,
                                    'yellow_cards' => $primaryStat->yellow_cards + $stat->yellow_cards,
                                    'red_cards' => $primaryStat->red_cards + $stat->red_cards,
                                ]);
                            // Delete the duplicate stat record
                            DB::table('player_season_stats')->where('id', $stat->id)->delete();
                        } else {
                            // Transfer ownership to primary player
                            DB::table('player_season_stats')
                                ->where('id', $stat->id)
                                ->update(['player_id' => $primary->id]);
                        }
                    }

                    // Delete duplicate
                    $duplicate->delete();
                    $mergedCount++;
                }
            }

            DB::commit();
            
            Notification::make()
                ->title(__('messages.merged_successfully'))
                ->body('Успешно объединено ' . $mergedCount . ' дубликатов.')
                ->success()
                ->send();

            $this->loadDuplicates();
        } catch (\Exception $e) {
            DB::rollBack();
            Notification::make()
                ->title(__('messages.merge_error', ['message' => $e->getMessage()]))
                ->danger()
                ->send();
        }
    }

    public function mergeGroup($primaryId, $duplicateIds)
    {
        // empty for now
    }
}
