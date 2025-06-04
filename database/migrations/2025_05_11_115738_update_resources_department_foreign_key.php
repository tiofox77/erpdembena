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
        Schema::table('resources', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['department_id']);
            
            // Update the foreign key to reference maintenance_areas instead
            $table->foreign('department_id')->references('id')->on('maintenance_areas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resources', function (Blueprint $table) {
            // Drop the maintenance_areas foreign key constraint
            $table->dropForeign(['department_id']);
            
            // Restore the original foreign key constraint to departments
            $table->foreign('department_id')->references('id')->on('departments');
        });
    }
};
