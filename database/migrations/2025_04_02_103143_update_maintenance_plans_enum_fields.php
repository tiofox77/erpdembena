<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For MySQL
        if (DB::getDriverName() === 'mysql') {
            // Update the 'type' enum to include 'conditional'
            DB::statement("ALTER TABLE maintenance_plans MODIFY COLUMN type ENUM('preventive', 'corrective', 'predictive', 'conditional', 'other') DEFAULT 'preventive'");
            
            // Update the 'status' enum to include 'schedule'
            DB::statement("ALTER TABLE maintenance_plans MODIFY COLUMN status ENUM('pending', 'in_progress', 'completed', 'cancelled', 'schedule') DEFAULT 'pending'");
        }
        
        // For PostgreSQL or other databases that don't support ENUM directly
        // We'd need to use a different approach, but for this example, we'll focus on MySQL
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // For MySQL
        if (DB::getDriverName() === 'mysql') {
            // Revert the 'type' enum back to original values
            DB::statement("ALTER TABLE maintenance_plans MODIFY COLUMN type ENUM('preventive', 'corrective', 'predictive', 'other') DEFAULT 'preventive'");
            
            // Revert the 'status' enum back to original values
            DB::statement("ALTER TABLE maintenance_plans MODIFY COLUMN status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending'");
        }
    }
};
