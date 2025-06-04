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
        Schema::table('maintenance_correctives', function (Blueprint $table) {
            // Add new foreign key columns
            $table->foreignId('failure_mode_id')->nullable()->after('equipment_id')
                  ->constrained('failure_modes')->nullOnDelete();

            $table->foreignId('failure_cause_id')->nullable()->after('failure_mode_id')
                  ->constrained('failure_causes')->nullOnDelete();

            // Drop old string columns
            $table->dropColumn([
                'failure_mode',
                'failure_mode_category',
                'failure_cause',
                'failure_cause_category',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_correctives', function (Blueprint $table) {
            // Add back the original string columns
            $table->string('failure_mode')->nullable()->after('equipment_id');
            $table->string('failure_mode_category')->nullable()->after('failure_mode');
            $table->string('failure_cause')->nullable()->after('end_time');
            $table->string('failure_cause_category')->nullable()->after('failure_cause');

            // Drop the foreign key constraints and columns
            $table->dropForeign(['failure_mode_id']);
            $table->dropForeign(['failure_cause_id']);
            $table->dropColumn(['failure_mode_id', 'failure_cause_id']);
        });
    }
};
