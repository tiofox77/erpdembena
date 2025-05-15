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
            $table->foreignId('equipment_type_id')->nullable()->after('maintenance_equipment_id')
                  ->constrained('equipment_types')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_parts', function (Blueprint $table) {
            $table->dropForeign(['equipment_type_id']);
            $table->dropColumn('equipment_type_id');
        });
    }
};
