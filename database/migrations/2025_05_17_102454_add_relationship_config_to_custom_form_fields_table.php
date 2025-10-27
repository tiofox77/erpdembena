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
        Schema::table('sc_custom_form_fields', function (Blueprint $table) {
            if (!Schema::hasColumn('sc_custom_form_fields', 'relationship_config')) {
                $table->json('relationship_config')->nullable()->after('options');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sc_custom_form_fields', function (Blueprint $table) {
            if (Schema::hasColumn('sc_custom_form_fields', 'relationship_config')) {
                $table->dropColumn('relationship_config');
            }
        });
    }
};
