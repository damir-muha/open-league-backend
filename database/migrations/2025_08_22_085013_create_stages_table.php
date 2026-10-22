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
        // 7. Таблица Этапов (Групповой, Плей-офф)
        // Файл: database/migrations/YYYY_MM_DD_HHMMSS_create_stages_table.php
        Schema::create('stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->enum('type', ['league', 'group', 'knockout']);
            $table->unsignedTinyInteger('order')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stages');
    }
};
