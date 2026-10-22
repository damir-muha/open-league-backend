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
        Schema::table('players', function (Blueprint $table) {
            // Добавляем новый безопасный идентификатор
            $table->string('registration_id')->unique()->nullable()->after('id');

            // Делаем поля необязательными
            $table->date('birth_date')->nullable()->change();
            $table->string('citizenship')->nullable()->change();
            $table->string('status')->nullable()->change();

            // Полностью удаляем столбец ИИН
            if (Schema::hasColumn('players', 'iin')) {
                $table->dropUnique('players_iin_unique');
                $table->dropColumn('iin');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            //
        });
    }
};
