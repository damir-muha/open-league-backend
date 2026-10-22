<?php
// Файл: ..._create_category_news_pivot_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('category_news', function (Blueprint $table) {
            $table->primary(['category_id', 'news_id']);
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('news_id')->constrained()->onDelete('cascade');
        });
    }
    public function down(): void { Schema::dropIfExists('category_news'); }
};