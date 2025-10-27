<?php

declare(strict_types=1);

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
        Schema::table('attendances', function (Blueprint $table) {
            // Campos para rastreamento de horas trabalhadas e valor
            $table->decimal('hourly_rate', 10, 2)->nullable()->after('is_approved');
            $table->decimal('overtime_hours', 5, 2)->nullable()->after('hourly_rate');
            $table->decimal('overtime_rate', 10, 2)->nullable()->after('overtime_hours');
            
            // Campos para rastreamento específico para mulheres
            $table->boolean('is_maternity_related')->default(false)->after('overtime_rate');
            $table->string('maternity_type')->nullable()->after('is_maternity_related');
            
            // Campo para marcar se este registo deve ser considerado para pagamento
            $table->boolean('affects_payroll')->default(true)->after('maternity_type');
            
            // Campo para rastrear qual processamento de folha de pagamento incluiu este registo
            $table->unsignedBigInteger('payroll_id')->nullable()->after('affects_payroll');
            $table->foreign('payroll_id')->references('id')->on('payrolls')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['payroll_id']);
            $table->dropColumn([
                'hourly_rate',
                'overtime_hours',
                'overtime_rate',
                'is_maternity_related',
                'maternity_type',
                'affects_payroll',
                'payroll_id'
            ]);
        });
    }
};
