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
        Schema::table('technicians', function (Blueprint $table) {
            $table->foreignId('line_id')->nullable()->constrained('maintenance_lines')->nullOnDelete();
            $table->foreignId('area_id')->nullable()->constrained('maintenance_areas')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('technicians', function (Blueprint $table) {
            $table->dropConstrainedForeignId('line_id');
            $table->dropConstrainedForeignId('area_id');
        });
    }
};
