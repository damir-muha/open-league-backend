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
        // Проверяем, есть ли уже такая колонка
        if (!Schema::hasColumn('events', 'suspension_duration')) {
            Schema::table('events', function (Blueprint $table) {
                $table->integer('suspension_duration')->nullable()->after('minute');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
        $table->dropColumn('suspension_duration');
        });
    }
};
