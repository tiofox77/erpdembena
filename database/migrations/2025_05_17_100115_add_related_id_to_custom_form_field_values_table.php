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
        Schema::table('sc_custom_form_field_values', function (Blueprint $table) {
            $table->unsignedBigInteger('related_id')->nullable()->after('value');
            $table->string('related_type')->nullable()->after('related_id');
            
            // Index para melhorar performance de buscas
            $table->index(['related_id', 'related_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sc_custom_form_field_values', function (Blueprint $table) {
            $table->dropIndex(['related_id', 'related_type']);
            $table->dropColumn(['related_id', 'related_type']);
        });
    }
};
