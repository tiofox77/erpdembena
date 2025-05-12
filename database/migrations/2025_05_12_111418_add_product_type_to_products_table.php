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
        Schema::table('sc_products', function (Blueprint $table) {
            $table->enum('product_type', ['finished_product', 'raw_material'])->default('finished_product')->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sc_products', function (Blueprint $table) {
            $table->dropColumn('product_type');
        });
    }
};
