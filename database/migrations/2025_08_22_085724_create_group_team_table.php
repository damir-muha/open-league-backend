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
        // 17. Связь Группа <-> Команда
        // Файл: database/migrations/YYYY_MM_DD_HHMMSS_create_group_team_table.php
        Schema::create('group_team', function (Blueprint $table) {
            $table->primary(['group_id', 'team_id']);
            $table->foreignId('group_id')->constrained()->onDelete('cascade');
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_team');
    }
};
