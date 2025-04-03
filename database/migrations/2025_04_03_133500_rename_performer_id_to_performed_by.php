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
        Schema::table('equipment_maintenances', function (Blueprint $table) {
            $table->renameColumn('performer_id', 'performed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_maintenances', function (Blueprint $table) {
            $table->renameColumn('performed_by', 'performer_id');
        });
    }
};
