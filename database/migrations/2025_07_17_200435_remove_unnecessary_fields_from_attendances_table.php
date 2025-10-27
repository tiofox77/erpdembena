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
        // Usar SQL raw para evitar problemas com Doctrine DBAL e campos enum
        
        // Primeiro, verificar quais foreign keys existem
        $foreignKeys = DB::select("SELECT CONSTRAINT_NAME, COLUMN_NAME 
                                  FROM information_schema.KEY_COLUMN_USAGE 
                                  WHERE TABLE_NAME = 'attendances' 
                                  AND CONSTRAINT_NAME != 'PRIMARY' 
                                  AND TABLE_SCHEMA = DATABASE()");
        
        // Remover foreign keys relevantes
        foreach ($foreignKeys as $fk) {
            if (in_array($fk->COLUMN_NAME, ['approved_by', 'payroll_id'])) {
                DB::statement("ALTER TABLE attendances DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
            }
        }
        
        // Verificar quais colunas existem
        $columns = Schema::getColumnListing('attendances');
        $columnsToRemove = [
            'approved_by',
            'is_approved', 
            'overtime_hours',
            'overtime_rate',
            'is_maternity_related',
            'maternity_type',
            'payroll_id'
        ];
        
        // Remover colunas que existem
        foreach ($columnsToRemove as $column) {
            if (in_array($column, $columns)) {
                DB::statement("ALTER TABLE attendances DROP COLUMN {$column}");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Restaurar campos removidos
            $table->boolean('is_approved')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->decimal('overtime_hours', 8, 2)->nullable();
            $table->decimal('overtime_rate', 8, 2)->nullable();
            $table->boolean('is_maternity_related')->default(false);
            $table->string('maternity_type')->nullable();
            $table->foreignId('payroll_id')->nullable()->constrained('payrolls')->onDelete('set null');
        });
    }
};
