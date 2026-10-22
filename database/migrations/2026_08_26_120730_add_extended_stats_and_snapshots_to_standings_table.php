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
        Schema::table('standings', function (Blueprint $table) {
            $table->foreignId('round_id')->nullable()->after('team_id')->constrained()->nullOnDelete();
            $table->unsignedInteger('position')->default(0)->after('group_id');
            
            // Home stats
            $table->unsignedInteger('home_played')->default(0);
            $table->unsignedInteger('home_wins')->default(0);
            $table->unsignedInteger('home_draws')->default(0);
            $table->unsignedInteger('home_losses')->default(0);
            $table->unsignedInteger('home_goals_for')->default(0);
            $table->unsignedInteger('home_goals_against')->default(0);
            $table->unsignedInteger('home_points')->default(0);

            // Away stats
            $table->unsignedInteger('away_played')->default(0);
            $table->unsignedInteger('away_wins')->default(0);
            $table->unsignedInteger('away_draws')->default(0);
            $table->unsignedInteger('away_losses')->default(0);
            $table->unsignedInteger('away_goals_for')->default(0);
            $table->unsignedInteger('away_goals_against')->default(0);
            $table->unsignedInteger('away_points')->default(0);

            // Recent form
            $table->json('recent_form')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('standings', function (Blueprint $table) {
            $table->dropForeign(['round_id']);
            $table->dropColumn([
                'round_id',
                'position',
                'home_played', 'home_wins', 'home_draws', 'home_losses', 'home_goals_for', 'home_goals_against', 'home_points',
                'away_played', 'away_wins', 'away_draws', 'away_losses', 'away_goals_for', 'away_goals_against', 'away_points',
                'recent_form'
            ]);
        });
    }
};
