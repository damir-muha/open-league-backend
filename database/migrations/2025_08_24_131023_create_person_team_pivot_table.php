<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('person_team', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('people')->onDelete('cascade');
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->string('role')->nullable(); // Должность в этой команде, например, "Главный тренер"
            $table->timestamps();
            $table->unique(['person_id', 'team_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('person_team');
    }
};
