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
        Schema::table('mrp_production_schedules', function (Blueprint $table) {
            $table->boolean('stock_moved')->default(false)->after('status');
            $table->timestamp('stock_moved_at')->nullable()->after('stock_moved');
            $table->unsignedBigInteger('stock_moved_by')->nullable()->after('stock_moved_at');
            $table->foreign('stock_moved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mrp_production_schedules', function (Blueprint $table) {
            $table->dropForeign(['stock_moved_by']);
            $table->dropColumn(['stock_moved', 'stock_moved_at', 'stock_moved_by']);
        });
    }
};
