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
        Schema::create('mrp_demand_forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('sc_products')->onDelete('cascade');
            $table->date('forecast_date');
            $table->integer('forecast_quantity');
            $table->decimal('confidence_level', 5, 2)->nullable()->comment('Confidence level of the forecast (0-100%)');
            $table->enum('forecast_type', ['manual', 'automatic', 'adjusted'])->default('manual');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mrp_demand_forecasts');
    }
};
