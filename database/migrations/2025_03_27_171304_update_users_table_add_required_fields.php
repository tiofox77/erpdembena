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
        Schema::table('users', function (Blueprint $table) {
            // Rename 'name' to 'first_name' and add 'last_name'
            $table->renameColumn('name', 'first_name');
            $table->string('last_name')->after('first_name');

            // Add other required fields
            $table->string('phone')->nullable()->after('email');
            $table->string('role')->default('user')->after('phone');
            $table->string('department')->default('other')->after('role');
            $table->boolean('is_active')->default(true)->after('department');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert changes
            $table->renameColumn('first_name', 'name');
            $table->dropColumn(['last_name', 'phone', 'role', 'department', 'is_active']);
        });
    }
};
