<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            // 1. Самый важный индекс для "Календаря матчей"
            // Позволяет мгновенно выбирать матчи сезона и сортировать их по дате
            $table->index(['season_id', 'game_datetime'], 'games_season_date_idx');

            // 2. Индекс для фильтрации по статусу (Алдағы / Өткен)
            // Ускоряет запросы вида: "Дай мне все завершенные матчи сезона, отсортированные по дате"
            $table->index(['season_id', 'status', 'game_datetime'], 'games_season_status_date_idx');

            // 3. Индексы для экрана "Профиль команды" (TeamProfile)
            // Ускоряют загрузку истории матчей конкретной команды
            $table->index(['home_team_id', 'game_datetime'], 'games_home_team_date_idx');
            $table->index(['away_team_id', 'game_datetime'], 'games_away_team_date_idx');
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropIndex('games_season_date_idx');
            $table->dropIndex('games_season_status_date_idx');
            $table->dropIndex('games_home_team_date_idx');
            $table->dropIndex('games_away_team_date_idx');
        });
    }
};