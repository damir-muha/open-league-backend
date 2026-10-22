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
        // 18. Связь Заявка на сезон <-> Игрок
        // Файл: database/migrations/YYYY_MM_DD_HHMMSS_create_player_season_roster_table.php
        Schema::create('player_season_roster', function (Blueprint $table) {
            $table->primary(['season_roster_id', 'player_id']);
            $table->foreignId('season_roster_id')->constrained()->onDelete('cascade');
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            $table->foreignId('season_id'); // Дублируем для уникального индекса
            $table->unique(['season_id', 'player_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_season_roster');
    }
};
