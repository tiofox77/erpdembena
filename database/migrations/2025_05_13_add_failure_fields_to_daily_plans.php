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
        Schema::table('mrp_production_daily_plans', function (Blueprint $table) {
            // Add breakdown flag and duration tracking
            $table->boolean('has_breakdown')->default(false)->after('actual_quantity');
            $table->integer('breakdown_minutes')->nullable()->after('has_breakdown');
            
            // Create a relation to failure category and handle multiple root causes
            $table->foreignId('failure_category_id')->nullable()->after('breakdown_minutes')
                ->constrained('mrp_failure_categories')->nullOnDelete();
            
            // Add a JSON field to store multiple root causes
            $table->json('failure_root_causes')->nullable()->after('failure_category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mrp_production_daily_plans', function (Blueprint $table) {
            $table->dropForeign(['failure_category_id']);
            $table->dropColumn([
                'has_breakdown',
                'breakdown_minutes',
                'failure_category_id',
                'failure_root_causes'
            ]);
        });
    }
};
