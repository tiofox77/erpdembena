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
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('equipment_type', ['computer', 'phone', 'tool', 'vehicle', 'other']);
            $table->string('serial_number')->nullable();
            $table->string('asset_code')->unique();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 10, 2)->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->string('condition')->nullable();
            $table->enum('status', ['available', 'assigned', 'maintenance', 'damaged', 'disposed']);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
