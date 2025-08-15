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
        Schema::table('employees', function (Blueprint $table) {
            // Add bank relationship and IBAN field
            $table->foreignId('bank_id')->nullable()->after('photo')->constrained('banks')->onDelete('set null');
            $table->string('bank_iban')->nullable()->after('bank_account');
            
            // Keep existing bank_name and bank_account for backwards compatibility
            // but these will be gradually phased out in favor of bank_id relationship
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['bank_id']);
            $table->dropColumn(['bank_id', 'bank_iban']);
        });
    }
};
