<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 10. Таблица Матчей
        // Файл: database/migrations/YYYY_MM_DD_HHMMSS_create_games_table.php
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->onDelete('cascade');
            $table->foreignId('stage_id')->constrained()->onDelete('cascade');
            $table->foreignId('round_id')->constrained()->onDelete('cascade');
            $table->foreignId('group_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('home_team_id')->constrained('teams')->onDelete('cascade');
            $table->foreignId('away_team_id')->constrained('teams')->onDelete('cascade');
            $table->foreignId('venue_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('referee_id')->nullable()->constrained('people')->onDelete('set null');
            $table->dateTime('game_datetime');
            $table->enum('status', ['scheduled', 'live', 'finished', 'postponed'])->default('scheduled');
            $table->unsignedSmallInteger('home_score')->nullable();
            $table->unsignedSmallInteger('away_score')->nullable();
            $table->foreignId('winner_to_game_id')->nullable()->constrained('games')->onDelete('set null');
            $table->foreignId('loser_to_game_id')->nullable()->constrained('games')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
