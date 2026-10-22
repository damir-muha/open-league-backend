<?php
// Файл: ..._create_news_player_pivot_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('news_player', function (Blueprint $table) {
            $table->primary(['news_id', 'player_id']);
            $table->foreignId('news_id')->constrained()->onDelete('cascade');
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
        });
    }
    public function down(): void { Schema::dropIfExists('news_player'); }
};