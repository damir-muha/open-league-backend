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
        Schema::create('Advertisements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('ad_type', ['banner', 'adsense']);
            $table->string('placement_area', 100);
            $table->text('image_url')->nullable();
            $table->text('target_url')->nullable();
            $table->text('code_snippet')->nullable();
            $table->boolean('is_active')->default(false);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();

            // Составной индекс для быстрой выборки активных баннеров
            $table->index(['placement_area', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Advertisements');
    }
};
