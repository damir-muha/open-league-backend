<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tournament;
use App\Models\Season;
use App\Models\Stage;
use App\Models\Team;
use App\Models\Game;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $tournament = Tournament::create(['name' => 'Premier League', 'slug' => 'premier-league', 'format' => 'league']);
        
        $season = Season::create([
            'tournament_id' => $tournament->id,
            'name' => '2026/2027',
            'start_date' => now()->subDays(10),
            'end_date' => now()->addMonths(3),
            'is_current' => true,
        ]);

        $stage = Stage::create([
            'season_id' => $season->id,
            'name' => 'Regular Season',
            'type' => 'league',
            'order' => 1
        ]);

        $team1 = Team::create(['name' => 'FC Astana', 'short_name' => 'AST', 'city' => 'Astana']);
        $team2 = Team::create(['name' => 'Kairat', 'short_name' => 'KRT', 'city' => 'Almaty']);

        $season->teams()->attach([$team1->id, $team2->id]);

        Game::create([
            'season_id' => $season->id,
            'stage_id' => $stage->id,
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'datetime' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'status' => 'scheduled',
        ]);
    }
}
