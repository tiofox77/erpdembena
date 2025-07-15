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
        Schema::create('salary_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('request_date');
            $table->decimal('amount', 12, 2)->comment('Montante do adiantamento');
            $table->unsignedInteger('installments')->default(1)->comment('Número de parcelas para desconto');
            $table->decimal('installment_amount', 12, 2)->comment('Valor de cada parcela');
            $table->date('first_deduction_date')->comment('Data da primeira dedução');
            $table->unsignedInteger('remaining_installments')->comment('Parcelas restantes a serem descontadas');
            $table->string('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->date('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Índices para melhorar a performance
            $table->index('request_date');
            $table->index('status');
            $table->index('remaining_installments');
        });
        
        // Tabela para registar os descontos aplicados
        Schema::create('salary_advance_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_advance_id')->constrained()->onDelete('cascade');
            $table->date('payment_date');
            $table->decimal('amount', 12, 2);
            $table->unsignedInteger('installment_number');
            $table->foreignId('processed_by')->nullable()->constrained('users');
            $table->timestamps();
            
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_advance_payments');
        Schema::dropIfExists('salary_advances');
    }
};
