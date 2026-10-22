<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('seasons', function (Blueprint $table) {
            $table->unsignedInteger('yellow_cards_for_suspension')->default(3)->after('is_current');
        });
    }
    public function down(): void {
        Schema::table('seasons', function (Blueprint $table) {
            $table->dropColumn('yellow_cards_for_suspension');
        });
    }
};