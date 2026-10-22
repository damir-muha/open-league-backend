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
        Schema::table('groups', function (Blueprint $table) {
            $table->foreignId('inherits_from_group_id')->nullable()->constrained('groups')->nullOnDelete();
            // modes: none, full, halved_up, halved_down
            $table->string('inherit_mode')->default('none');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropForeign(['inherits_from_group_id']);
            $table->dropColumn(['inherits_from_group_id', 'inherit_mode']);
        });
    }
};
