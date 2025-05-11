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
        Schema::create('mrp_bom_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_header_id')->constrained('mrp_bom_headers')->onDelete('cascade');
            $table->foreignId('component_id')->constrained('sc_products')->comment('Component or raw material');
            $table->decimal('quantity', 10, 3);
            $table->enum('uom', ['unit', 'kg', 'l', 'g', 'ml', 'pcs'])->default('unit');
            $table->string('position')->nullable()->comment('Position in assembly');
            $table->integer('level')->default(1)->comment('Level in BOM structure');
            $table->decimal('scrap_percentage', 5, 2)->default(0)->comment('Expected scrap percentage');
            $table->boolean('is_critical')->default(false);
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
        Schema::dropIfExists('mrp_bom_details');
    }
};
