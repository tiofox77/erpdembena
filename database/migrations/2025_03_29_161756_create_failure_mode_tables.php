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
        // Create failure mode categories table
        if (!Schema::hasTable('failure_mode_categories')) {
            Schema::create('failure_mode_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        } else {
            // Add soft delete to existing table if it doesn't have it
            if (!Schema::hasColumn('failure_mode_categories', 'deleted_at')) {
                Schema::table('failure_mode_categories', function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }

        // Create failure modes table
        if (!Schema::hasTable('failure_modes')) {
            Schema::create('failure_modes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('category_id')->nullable()->constrained('failure_mode_categories')->nullOnDelete();
                $table->string('name');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        } else {
            // Add soft delete to existing table if it doesn't have it
            if (!Schema::hasColumn('failure_modes', 'deleted_at')) {
                Schema::table('failure_modes', function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('failure_modes');
        Schema::dropIfExists('failure_mode_categories');
    }
};
