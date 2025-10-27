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
        Schema::create('shipping_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('sc_purchase_orders')->onDelete('cascade');
            $table->enum('status', [
                'order_placed',
                'proforma_invoice_received',
                'payment_completed',
                'du_in_process',
                'goods_acquired',
                'shipped_to_port',
                'shipping_line_booking_confirmed',
                'container_loaded',
                'on_board',
                'arrived_at_port',
                'customs_clearance',
                'delivered'
            ]);
            $table->text('note')->nullable();
            $table->string('attachment_url')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_notes');
    }
};
