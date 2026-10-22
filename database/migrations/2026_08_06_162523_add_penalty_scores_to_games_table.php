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
        Schema::table('games', function (Blueprint $table) {
            $table->unsignedSmallInteger('home_penalty_score')->nullable()->after('away_score');
            $table->unsignedSmallInteger('away_penalty_score')->nullable()->after('home_penalty_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn(['home_penalty_score', 'away_penalty_score']);
        });
    }
};
