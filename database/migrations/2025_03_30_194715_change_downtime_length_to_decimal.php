<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('maintenance_correctives', function (Blueprint $table) {
            $table->decimal('downtime_length', 10, 2)->change();
        });
    }

    public function down()
    {
        Schema::table('maintenance_correctives', function (Blueprint $table) {
            // Defina o tipo antigo, por exemplo:
            $table->string('downtime_length')->change();
        });
    }
};
