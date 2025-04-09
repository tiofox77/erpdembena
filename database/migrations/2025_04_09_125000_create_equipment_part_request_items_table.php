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
        Schema::create('equipment_part_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('equipment_part_requests')->onDelete('cascade');
            $table->foreignId('equipment_part_id')->constrained('equipment_parts');
            $table->integer('quantity_required')->default(1);
            $table->string('unit', 50)->default('pcs');
            $table->string('supplier_reference')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_part_request_items');
    }
};
