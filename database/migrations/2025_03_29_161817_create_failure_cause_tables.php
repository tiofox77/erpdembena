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
        // Create failure cause categories table
        if (!Schema::hasTable('failure_cause_categories')) {
            Schema::create('failure_cause_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        } else {
            // Add soft delete to existing table if it doesn't have it
            if (!Schema::hasColumn('failure_cause_categories', 'deleted_at')) {
                Schema::table('failure_cause_categories', function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }

        // Create failure causes table
        if (!Schema::hasTable('failure_causes')) {
            Schema::create('failure_causes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('category_id')->nullable()->constrained('failure_cause_categories')->nullOnDelete();
                $table->string('name');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        } else {
            // Add soft delete to existing table if it doesn't have it
            if (!Schema::hasColumn('failure_causes', 'deleted_at')) {
                Schema::table('failure_causes', function (Blueprint $table) {
                    $table->softDeletes();
                });
            }

            // Add category_id if it doesn't exist
            if (!Schema::hasColumn('failure_causes', 'category_id')) {
                Schema::table('failure_causes', function (Blueprint $table) {
                    $table->foreignId('category_id')->nullable()->after('id')
                        ->constrained('failure_cause_categories')->nullOnDelete();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('failure_causes');
        Schema::dropIfExists('failure_cause_categories');
    }
};
