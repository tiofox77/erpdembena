<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Esta é uma migração de teste para simular uma atualização do sistema
     */
    public function up(): void
    {
        Schema::create('test_updates', function (Blueprint $table) {
            $table->id();
            $table->string('version');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_applied')->default(false);
            $table->dateTime('applied_at')->nullable();
            $table->json('update_details')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_updates');
    }
};
