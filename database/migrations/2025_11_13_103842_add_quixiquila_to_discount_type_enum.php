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
        // Alterar o ENUM para incluir 'quixiquila'
        DB::statement("ALTER TABLE salary_discounts MODIFY COLUMN discount_type ENUM('union', 'others', 'quixiquila') NOT NULL DEFAULT 'others'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter para o ENUM original (sem 'quixiquila')
        DB::statement("ALTER TABLE salary_discounts MODIFY COLUMN discount_type ENUM('union', 'others') NOT NULL DEFAULT 'others'");
    }
};
