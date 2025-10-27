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
        Schema::table('leaves', function (Blueprint $table) {
            // Campos específicos para mulheres
            $table->boolean('is_gender_specific')->default(false)->after('attachment');
            $table->string('gender_leave_type')->nullable()->after('is_gender_specific');
            $table->text('medical_certificate_details')->nullable()->after('gender_leave_type');
            
            // Campos para cálculo de pagamento
            $table->boolean('is_paid_leave')->default(true)->after('medical_certificate_details');
            $table->decimal('payment_percentage', 5, 2)->default(100.00)->after('is_paid_leave');
            $table->text('payment_notes')->nullable()->after('payment_percentage');
            
            // Integração com folha de pagamento
            $table->boolean('affects_payroll')->default(true)->after('payment_notes');
            $table->unsignedBigInteger('payroll_id')->nullable()->after('affects_payroll');
            $table->foreign('payroll_id')->references('id')->on('payrolls')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->dropForeign(['payroll_id']);
            $table->dropColumn([
                'is_gender_specific',
                'gender_leave_type',
                'medical_certificate_details',
                'is_paid_leave',
                'payment_percentage',
                'payment_notes',
                'affects_payroll',
                'payroll_id'
            ]);
        });
    }
};
