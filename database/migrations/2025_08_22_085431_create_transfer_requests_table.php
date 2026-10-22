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
        // 13. Таблица Запросов на трансфер
        // Файл: database/migrations/YYYY_MM_DD_HHMMSS_create_transfer_requests_table.php
        Schema::create('transfer_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->onDelete('cascade');
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            $table->foreignId('from_team_id')->constrained('teams')->onDelete('cascade');
            $table->foreignId('to_team_id')->constrained('teams')->onDelete('cascade');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('requested_by_manager_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by_manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_requests');
    }
};
