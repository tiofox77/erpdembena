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
        // Verificar se as colunas existem antes de adicioná-las
        $columns = $this->getTableColumns('users');

        Schema::table('users', function (Blueprint $table) use ($columns) {
            // Rename 'name' to 'first_name' only if 'first_name' doesn't exist and 'name' exists
            if (!in_array('first_name', $columns) && in_array('name', $columns)) {
                $table->renameColumn('name', 'first_name');
            } elseif (!in_array('first_name', $columns) && !in_array('name', $columns)) {
                // Add first_name if neither exists
                $table->string('first_name')->nullable();
            }

            // Add last_name only if it doesn't exist
            if (!in_array('last_name', $columns)) {
                $table->string('last_name')->nullable();
            }

            // Add other fields only if they don't exist
            if (!in_array('phone', $columns)) {
                $table->string('phone')->nullable();
            }

            if (!in_array('role', $columns)) {
                $table->string('role')->default('user');
            }

            if (!in_array('department', $columns)) {
                $table->string('department')->default('other');
            }

            if (!in_array('is_active', $columns)) {
                $table->boolean('is_active')->default(true);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columns = $this->getTableColumns('users');

        Schema::table('users', function (Blueprint $table) use ($columns) {
            // Only revert changes for columns that exist
            if (in_array('first_name', $columns) && !in_array('name', $columns)) {
                $table->renameColumn('first_name', 'name');
            }

            // Drop columns if they exist
            $columnsToCheck = ['last_name', 'phone', 'role', 'department', 'is_active'];
            $columnsToDrop = [];

            foreach ($columnsToCheck as $column) {
                if (in_array($column, $columns)) {
                    $columnsToDrop[] = $column;
                }
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    /**
     * Get column names for a table
     */
    private function getTableColumns($table)
    {
        return Schema::hasTable($table)
            ? array_map('strtolower', Schema::getColumnListing($table))
            : [];
    }
};
