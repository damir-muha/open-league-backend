<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Удаляем старую колонку для одного судьи
        Schema::table('games', function (Blueprint $table) {
            if (Schema::hasColumn('games', 'referee_id')) {
                $table->dropForeign(['referee_id']);
                $table->dropColumn('referee_id');
            }
        });

        // Создаем новую промежуточную таблицу для бригады арбитров
        Schema::create('game_person', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->foreignId('person_id')->constrained('people')->onDelete('cascade');
            $table->string('role')->default('assistant_referee'); // Например: main_referee, assistant, VAR
            $table->unique(['game_id', 'person_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_person');
        Schema::table('games', function (Blueprint $table) {
            $table->foreignId('referee_id')->nullable()->constrained('people')->onDelete('set null');
        });
    }
};
