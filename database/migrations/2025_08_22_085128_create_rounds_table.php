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
        // 9. Таблица Туров (Раундов)
        // Файл: database/migrations/YYYY_MM_DD_HHMMSS_create_rounds_table.php
        Schema::create('rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stage_id')->constrained()->onDelete('cascade');
            $table->string('name'); // "1-й тур", "1/8 финала"
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rounds');
    }
};
