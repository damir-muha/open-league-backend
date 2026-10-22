<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Player;
use Illuminate\Support\Facades\DB;

class MergeDuplicatePlayers extends Command
{
    protected $signature = 'league:merge-duplicates {--dry-run : Execute without making actual changes}';
    protected $description = 'Finds duplicate players (by first/last name) and merges their stats into the oldest record.';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('Running in DRY-RUN mode. No changes will be saved to the database.');
        }

        // Find duplicates based on first_name and last_name
        $duplicates = Player::select('first_name', 'last_name')
            ->groupBy('first_name', 'last_name')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('No duplicate players found!');
            return;
        }

        $this->info("Found {$duplicates->count()} duplicate groups.");

        DB::beginTransaction();

        try {
            foreach ($duplicates as $group) {
                $players = Player::where('first_name', $group->first_name)
                    ->where('last_name', $group->last_name)
                    ->orderBy('id', 'asc')
                    ->get();

                $primary = $players->shift(); // The oldest one is primary

                $this->info("Group: {$primary->first_name} {$primary->last_name}");
                $this->info("  Primary ID: {$primary->id}");

                foreach ($players as $duplicate) {
                    $this->info("  Merging Duplicate ID: {$duplicate->id}");

                    if (!$dryRun) {
                        // 1. Update Events (player_id and related_player_id)
                        DB::table('events')->where('player_id', $duplicate->id)->update(['player_id' => $primary->id]);
                        DB::table('events')->where('related_player_id', $duplicate->id)->update(['related_player_id' => $primary->id]);

                        // 2. Update Suspensions
                        DB::table('suspensions')->where('player_id', $duplicate->id)->update(['player_id' => $primary->id]);

                        // 3. Update Transfer Requests
                        DB::table('transfer_requests')->where('player_id', $duplicate->id)->update(['player_id' => $primary->id]);

                        // 4. Update player_season_roster (Pivot table)
                        $rosters = DB::table('player_season_roster')->where('player_id', $duplicate->id)->get();
                        foreach ($rosters as $roster) {
                            // Check if primary is already in this roster/season
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
                                // Just delete the duplicate pivot entry
                                DB::table('player_season_roster')
                                    ->where('player_id', $duplicate->id)
                                    ->where('season_id', $roster->season_id)
                                    ->delete();
                            }
                        }

                        // Update Lineups
                        DB::table('game_lineups')->where('player_id', $duplicate->id)->update(['player_id' => $primary->id]);

                        // Delete the duplicate player
                        $duplicate->delete();
                    }
                }
            }

            if ($dryRun) {
                DB::rollBack();
                $this->info('Dry run completed. Rolled back.');
            } else {
                DB::commit();
                $this->info('Successfully merged all duplicates!');
                
                // ADD UNIQUE CONSTRAINT TO DB TO PREVENT FUTURE ONES
                $this->info('Now you should manually add a unique index to the DB or run migrations if applicable.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('An error occurred during merging: ' . $e->getMessage());
        }
    }
}
