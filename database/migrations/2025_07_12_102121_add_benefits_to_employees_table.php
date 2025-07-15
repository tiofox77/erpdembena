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
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('food_benefit', 12, 2)->nullable()->default(0)->comment('Benefício de alimentação mensal');
            $table->decimal('transport_benefit', 12, 2)->nullable()->default(0)->comment('Benefício de transporte mensal');
            $table->decimal('bonus_amount', 12, 2)->nullable()->default(0)->comment('Bônus opcional mensal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'food_benefit',
                'transport_benefit',
                'bonus_amount'
            ]);
        });
    }
};
