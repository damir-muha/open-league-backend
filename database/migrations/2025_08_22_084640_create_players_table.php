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
        // 3. Таблица Игроков (глобальная)
        // Файл: database/migrations/YYYY_MM_DD_HHMMSS_create_players_table.php
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->string('iin')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('photo')->nullable();
            $table->date('birth_date');
            $table->string('citizenship');
            $table->string('position')->nullable(); // Можно сделать отдельной таблицей-справочником
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
