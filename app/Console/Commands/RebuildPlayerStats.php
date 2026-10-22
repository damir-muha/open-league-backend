<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PlayerSeasonStat;
use App\Models\Game;
use App\Models\Event;
use Illuminate\Support\Facades\DB;

class RebuildPlayerStats extends Command
{
    protected $signature = 'league:rebuild-stats';
    protected $description = 'Rebuilds all player_season_stats from game_lineups and events.';

    public function handle()
    {
        $this->info('Starting stat rebuild...');

        DB::beginTransaction();

        try {
            // 1. Clear existing stats
            PlayerSeasonStat::truncate();
            $this->info('Cleared existing stats.');

            // 2. Rebuild games_played
            $games = Game::where('status', 'finished')->with('lineups')->get();
            $this->info('Processing ' . $games->count() . ' played games for lineups...');

            foreach ($games as $game) {
                foreach ($game->lineups as $lineup) {
                    $stat = PlayerSeasonStat::firstOrCreate([
                        'player_id' => $lineup->player_id,
                        'season_id' => $game->season_id,
                        'team_id'   => $lineup->team_id,
                    ]);
                    $stat->increment('games_played');
                }
            }

            // 3. Rebuild goals, assists, cards
            $events = Event::with('game')->get();
            $this->info('Processing ' . $events->count() . ' events...');

            foreach ($events as $event) {
                if (!$event->player_id || !$event->team_id || !$event->game) {
                    continue;
                }

                $stat = PlayerSeasonStat::firstOrCreate([
                    'player_id' => $event->player_id,
                    'season_id' => $event->game->season_id,
                    'team_id'   => $event->team_id,
                ]);

                $column = match ($event->type) {
                    'goal'                 => 'goals',
                    'assist'               => 'assists',
                    'yellow_card'          => 'yellow_cards',
                    'second_yellow_card'   => 'red_cards',
                    'red_card'             => 'red_cards',
                    default                => null,
                };

                if ($column) {
                    $stat->increment($column);
                }
            }

            DB::commit();
            $this->info('Successfully rebuilt all player stats!');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Failed to rebuild stats: ' . $e->getMessage());
        }
    }
}
