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
        Schema::create('suspensions', function (Blueprint $table) {
        $table->id();
        // Связи
        $table->foreignId('player_id')->constrained()->cascadeOnDelete();
        $table->foreignId('season_id')->constrained()->cascadeOnDelete();
        $table->foreignId('game_id')->nullable()->constrained()->nullOnDelete(); // Матч, где получена КК
        
        // Данные
        $table->string('reason'); // Причина: 'red_card', 'yellow_card_accumulation' и т.д.
        $table->integer('duration')->default(1); // На сколько матчей
        $table->integer('served')->default(0);   // Сколько уже пропущено
        $table->boolean('is_active')->default(true); // Активна ли сейчас
        
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suspensions');
    }
};
