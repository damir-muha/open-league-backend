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
        Schema::table('player_season_roster', function (Blueprint $table) {
            // Добавляем поле для номера после player_id
            $table->unsignedSmallInteger('jersey_number')->nullable()->after('player_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('player_season_roster', function (Blueprint $table) {
            $table->dropColumn('jersey_number');
        });
    }
};

