<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ИСПРАВЛЕНО: Название таблицы в snake_case
        Schema::create('game_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->onDelete('cascade');
            $table->enum('media_type', ['photo', 'youtube_video']);
            $table->text('url');
            $table->string('title')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // ИСПРАВЛЕНО: Название таблицы в snake_case
        Schema::dropIfExists('game_media');
    }
};
