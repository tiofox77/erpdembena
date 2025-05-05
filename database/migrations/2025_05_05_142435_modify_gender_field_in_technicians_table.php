<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('technicians', function (Blueprint $table) {
            // Modificar o campo gender para aceitar valores nulos
            DB::statement("ALTER TABLE `technicians` MODIFY `gender` ENUM('male', 'female', 'other') NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('technicians', function (Blueprint $table) {
            // Reverter a mudança, fazendo o campo gender não aceitar mais valores nulos
            DB::statement("ALTER TABLE `technicians` MODIFY `gender` ENUM('male', 'female', 'other') NOT NULL");
        });
    }
};
