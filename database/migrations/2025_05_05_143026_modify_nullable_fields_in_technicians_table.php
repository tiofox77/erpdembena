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
        // Modificar os campos opcionais para aceitarem valores NULL
        DB::statement("ALTER TABLE `technicians` MODIFY `phone_number` VARCHAR(255) NULL");
        DB::statement("ALTER TABLE `technicians` MODIFY `address` TEXT NULL");
        DB::statement("ALTER TABLE `technicians` MODIFY `age` INT NULL");
        DB::statement("ALTER TABLE `technicians` MODIFY `line_id` BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE `technicians` MODIFY `area_id` BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE `technicians` MODIFY `function` VARCHAR(255) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter as mudanças, fazendo os campos voltarem a ser NOT NULL
        DB::statement("ALTER TABLE `technicians` MODIFY `phone_number` VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE `technicians` MODIFY `address` TEXT NOT NULL");
        DB::statement("ALTER TABLE `technicians` MODIFY `age` INT NOT NULL");
        DB::statement("ALTER TABLE `technicians` MODIFY `line_id` BIGINT UNSIGNED NOT NULL");
        DB::statement("ALTER TABLE `technicians` MODIFY `area_id` BIGINT UNSIGNED NOT NULL");
        DB::statement("ALTER TABLE `technicians` MODIFY `function` VARCHAR(255) NOT NULL");
    }
};
