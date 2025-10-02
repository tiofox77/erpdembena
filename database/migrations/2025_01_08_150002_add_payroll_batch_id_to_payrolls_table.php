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
        Schema::table('payrolls', function (Blueprint $table) {
            $table->foreignId('payroll_batch_id')
                  ->nullable()
                  ->after('payroll_period_id')
                  ->constrained('payroll_batches')
                  ->onDelete('set null');
            
            $table->index('payroll_batch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropForeign(['payroll_batch_id']);
            $table->dropIndex(['payroll_batch_id']);
            $table->dropColumn('payroll_batch_id');
        });
    }
};
