<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Doctrine\DBAL\Types\Type;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Modificar campo name para ser nullable, já que usaremos first_name e last_name
            $table->string('name')->nullable()->change();

            // Adicionar novos campos
            $table->string('first_name')->after('name')->nullable();
            $table->string('last_name')->after('first_name')->nullable();
            $table->string('phone')->after('email_verified_at')->nullable();
            $table->enum('role', ['admin', 'manager', 'supervisor', 'technician', 'employee', 'user'])->after('phone')->default('user');
            $table->string('department')->after('role')->nullable();
            $table->boolean('is_active')->after('department')->default(true);

            // Adicionar soft deletes
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Não tente mudar a condição de nullable para o campo name
            // $table->string('name')->nullable(false)->change();

            // Remover os campos adicionados
            $table->dropColumn([
                'first_name',
                'last_name',
                'phone',
                'role',
                'department',
                'is_active',
                'deleted_at'
            ]);
        });
    }
};
