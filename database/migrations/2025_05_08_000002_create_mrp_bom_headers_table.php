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
        Schema::create('mrp_bom_headers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('sc_products')->onDelete('cascade');
            $table->string('bom_number')->unique();
            $table->string('description');
            $table->enum('status', ['draft', 'active', 'obsolete'])->default('draft');
            $table->date('effective_date');
            $table->date('expiration_date')->nullable();
            $table->integer('version')->default(1);
            $table->enum('uom', ['unit', 'kg', 'l', 'g', 'ml', 'pcs'])->default('unit');
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
        Schema::dropIfExists('mrp_bom_headers');
    }
};
