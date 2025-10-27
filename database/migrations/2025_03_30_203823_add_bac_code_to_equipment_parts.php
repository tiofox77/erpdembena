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
        Schema::table('equipment_parts', function (Blueprint $table) {
            $table->string('bar_code')->nullable()->after('part_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_parts', function (Blueprint $table) {
            $table->dropColumn('bar_code');
        });
    }
};
