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
        Schema::create('mrp_financial_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_number')->unique();
            $table->string('title');
            $table->enum('report_type', ['inventory_cost', 'production_cost', 'purchase_cost', 'overall'])->default('overall');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_material_cost', 12, 2)->default(0);
            $table->decimal('total_labor_cost', 12, 2)->default(0);
            $table->decimal('total_overhead_cost', 12, 2)->default(0);
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->decimal('average_inventory_value', 12, 2)->default(0)->nullable();
            $table->decimal('inventory_turnover_rate', 6, 2)->default(0)->nullable();
            $table->jsonb('cost_breakdown')->nullable()->comment('Detalhamento de custos em formato JSON');
            $table->enum('status', ['draft', 'finalized', 'approved'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mrp_financial_reports');
    }
};
