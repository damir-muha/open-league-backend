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
        // 5. Таблица Персонала (Тренеры, судьи и т.д.)
        // Файл: database/migrations/YYYY_MM_DD_HHMMSS_create_people_table.php
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('role'); // Например: 'coach', 'referee', 'admin'
            $table->string('photo')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('citizenship')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('people');
    }
};
