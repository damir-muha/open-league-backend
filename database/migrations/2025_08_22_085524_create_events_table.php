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
        // 14. Таблица Событий в матче
        // Файл: database/migrations/YYYY_MM_DD_HHMMSS_create_events_table.php
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            $table->foreignId('team_id')->constrained()->onDelete('cascade'); // За какую команду совершено действие
            $table->string('type'); // 'goal', 'yellow_card', 'red_card', 'substitution_in', 'assist'
            $table->unsignedTinyInteger('minute');
            $table->foreignId('related_player_id')->nullable()->constrained('players')->onDelete('set null'); // Для замен
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
