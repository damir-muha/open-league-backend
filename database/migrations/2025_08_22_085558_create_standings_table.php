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
        // 15. Таблица Турнирных таблиц (кэшированная)
        // Файл: database/migrations/YYYY_MM_DD_HHMMSS_create_standings_table.php
        Schema::create('standings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->onDelete('cascade');
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('group_id')->nullable()->constrained()->onDelete('cascade');
            $table->unsignedInteger('played')->default(0);
            $table->unsignedInteger('wins')->default(0);
            $table->unsignedInteger('draws')->default(0);
            $table->unsignedInteger('losses')->default(0);
            $table->unsignedInteger('goals_for')->default(0);
            $table->unsignedInteger('goals_against')->default(0);
            $table->integer('goal_difference')->default(0);
            $table->unsignedInteger('points')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('standings');
    }
};
