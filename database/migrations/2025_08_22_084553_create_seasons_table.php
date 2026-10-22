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
        // 2. Таблица Сезонов
        // Файл: database/migrations/YYYY_MM_DD_HHMMSS_create_seasons_table.php
        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('regulations')->nullable();
            $table->text('description')->nullable();
            $table->json('ranking_rules')->nullable();
            $table->enum('format_type', ['league', 'group_and_playoff', 'knockout']);
            $table->unsignedTinyInteger('roster_min_players')->default(15);
            $table->unsignedTinyInteger('roster_max_players')->default(25);
            $table->unsignedTinyInteger('players_on_field')->default(11);
            $table->unsignedTinyInteger('substitutes_on_bench')->default(7);
            $table->dateTime('roster_deadline');
            $table->dateTime('transfer_window_start');
            $table->dateTime('transfer_window_end');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seasons');
    }
};
