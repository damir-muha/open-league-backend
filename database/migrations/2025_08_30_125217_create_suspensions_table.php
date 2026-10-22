<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('suspensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            $table->foreignId('season_id')->constrained()->onDelete('cascade');
            $table->foreignId('game_id')->nullable()->constrained()->onDelete('set null');
            $table->string('reason');
            $table->unsignedInteger('duration');
            $table->unsignedInteger('served')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('suspensions');
    }
};