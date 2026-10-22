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
        // 8. Таблица Групп
        // Файл: database/migrations/YYYY_MM_DD_HHMMSS_create_groups_table.php
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stage_id')->constrained()->onDelete('cascade');
            $table->string('name'); // "Группа А"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
