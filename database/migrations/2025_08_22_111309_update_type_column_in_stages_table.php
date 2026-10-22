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
        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE stages DROP CONSTRAINT IF EXISTS stages_type_check");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE stages ADD CONSTRAINT stages_type_check CHECK (type::text IN ('league', 'group', 'knockout'))");
        } else {
            Schema::table('stages', function (Blueprint $table) {
                // Изменяем колонку, добавляя новое значение
                $table->enum('type', ['league', 'group', 'knockout'])->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stages', function (Blueprint $table) {
            // Возвращаем как было, если понадобится откат
            $table->enum('type', ['group', 'knockout'])->change();
        });
    }
};