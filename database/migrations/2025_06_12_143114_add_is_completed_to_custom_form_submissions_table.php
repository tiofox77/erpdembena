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
        Schema::table('sc_custom_form_submissions', function (Blueprint $table) {
            $table->boolean('is_completed')->default(false)->after('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sc_custom_form_submissions', function (Blueprint $table) {
            $table->dropColumn('is_completed');
        });
    }
};
