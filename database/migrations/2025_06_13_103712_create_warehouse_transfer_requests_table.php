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
        Schema::create('sc_warehouse_transfer_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->unsignedBigInteger('from_warehouse_id');
            $table->unsignedBigInteger('to_warehouse_id');
            $table->text('notes')->nullable();
            $table->string('status')->default('draft');
            $table->string('priority')->default('normal');
            $table->date('requested_date');
            $table->date('required_date')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('requested_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('from_warehouse_id')
                  ->references('id')
                  ->on('sc_inventory_locations')
                  ->onDelete('restrict');
                  
            $table->foreign('to_warehouse_id')
                  ->references('id')
                  ->on('sc_inventory_locations')
                  ->onDelete('restrict');
                  
            $table->foreign('requested_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict');
                  
            $table->foreign('approved_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
            
            // Indexes
            $table->index('status', 'wtr_status_idx');
            $table->index('requested_date', 'wtr_requested_date_idx');
            $table->index('from_warehouse_id', 'wtr_from_warehouse_idx');
            $table->index('to_warehouse_id', 'wtr_to_warehouse_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_warehouse_transfer_requests');
    }
};
