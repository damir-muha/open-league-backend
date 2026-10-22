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
        // 16. Связь Сезон <-> Команда
        // Файл: database/migrations/YYYY_MM_DD_HHMMSS_create_season_team_table.php
        Schema::create('season_team', function (Blueprint $table) {
            $table->primary(['season_id', 'team_id']);
            $table->foreignId('season_id')->constrained()->onDelete('cascade');
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('season_team');
    }
};
