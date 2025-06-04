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
        if (!Schema::hasTable('maintenance_equipment')) {
            Schema::create('maintenance_equipment', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique()->comment('Equipment identification code');
                $table->string('name');
                $table->string('type')->nullable();
                $table->string('area')->nullable();
                $table->string('department')->nullable();
                $table->string('location')->nullable();
                $table->string('model')->nullable();
                $table->string('manufacturer')->nullable();
                $table->string('serial_number')->nullable();
                $table->year('year_of_manufacture')->nullable();
                $table->date('installation_date')->nullable();
                $table->text('description')->nullable();
                $table->text('specifications')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_equipment');
    }
};
