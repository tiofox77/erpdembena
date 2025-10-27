<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsActiveToPurchaseOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sc_purchase_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('sc_purchase_orders', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sc_purchase_orders', function (Blueprint $table) {
            if (Schema::hasColumn('sc_purchase_orders', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
}
