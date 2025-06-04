<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeletedAtToMrpProductionScheduleShiftTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mrp_production_schedule_shift', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mrp_production_schedule_shift', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}
