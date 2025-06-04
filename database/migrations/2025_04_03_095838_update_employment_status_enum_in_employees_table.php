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
        // For MySQL/MariaDB
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE employees MODIFY employment_status ENUM('active', 'on_leave', 'terminated', 'suspended', 'retired') NOT NULL");
        }
        // For PostgreSQL
        else if (DB::connection()->getDriverName() === 'pgsql') {
            // Drop existing constraint
            DB::statement("ALTER TABLE employees DROP CONSTRAINT IF EXISTS employees_employment_status_check");
            
            // Add new constraint
            DB::statement("ALTER TABLE employees ADD CONSTRAINT employees_employment_status_check CHECK (employment_status IN ('active', 'on_leave', 'terminated', 'suspended', 'retired'))");
        }
        // For SQLite
        else if (DB::connection()->getDriverName() === 'sqlite') {
            // SQLite doesn't support modifying columns, so for this we'd need to create a new table and migrate data
            // This is a simplified example
            Schema::table('employees', function (Blueprint $table) {
                // SQLite doesn't have ENUMs, it uses CHECK constraints
                DB::statement("UPDATE employees SET employment_status = 'active' WHERE employment_status NOT IN ('active', 'on_leave', 'terminated', 'suspended', 'retired')");
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse since we're correcting data to match the expected format
    }
};
