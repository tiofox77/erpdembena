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
        // Tabela principal para os formulários personalizados
        Schema::create('sc_custom_forms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('entity_type')->default('shipping_note'); // Para expansão futura
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('created_by')->references('id')->on('users');
        });
        
        // Tabela para os campos dos formulários
        Schema::create('sc_custom_form_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('form_id');
            $table->string('label');
            $table->string('name');
            $table->string('type'); // text, textarea, select, date, file, etc.
            $table->text('options')->nullable(); // Para os campos do tipo select
            $table->text('validation_rules')->nullable(); // Regras de validação do Laravel
            $table->text('description')->nullable();
            $table->boolean('is_required')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->foreign('form_id')->references('id')->on('sc_custom_forms')->onDelete('cascade');
        });
        
        // Tabela para os envios de formulários
        Schema::create('sc_custom_form_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('form_id');
            $table->unsignedBigInteger('entity_id'); // ID da shipping_note
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            $table->foreign('form_id')->references('id')->on('sc_custom_forms');
            $table->foreign('created_by')->references('id')->on('users');
        });
        
        // Tabela para os valores dos campos enviados
        Schema::create('sc_custom_form_field_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('submission_id');
            $table->unsignedBigInteger('field_id');
            $table->text('value')->nullable();
            $table->timestamps();
            
            $table->foreign('submission_id')->references('id')->on('sc_custom_form_submissions')->onDelete('cascade');
            $table->foreign('field_id')->references('id')->on('sc_custom_form_fields')->onDelete('cascade');
        });
        
        // Tabela para os arquivos anexados
        Schema::create('sc_custom_form_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('field_value_id');
            $table->string('filename');
            $table->string('original_filename');
            $table->string('mime_type');
            $table->string('path');
            $table->integer('size')->comment('Tamanho em bytes');
            $table->timestamps();
            
            $table->foreign('field_value_id')->references('id')->on('sc_custom_form_field_values')->onDelete('cascade');
        });
        
        // Adicionando coluna à tabela de shipping_notes para o formulário atualmente associado
        Schema::table('shipping_notes', function (Blueprint $table) {
            $table->unsignedBigInteger('custom_form_id')->nullable()->after('status');
            $table->foreign('custom_form_id')->references('id')->on('sc_custom_forms')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover a coluna custom_form_id da tabela shipping_notes
        Schema::table('shipping_notes', function (Blueprint $table) {
            $table->dropForeign(['custom_form_id']);
            $table->dropColumn('custom_form_id');
        });
        
        Schema::dropIfExists('sc_custom_form_attachments');
        Schema::dropIfExists('sc_custom_form_field_values');
        Schema::dropIfExists('sc_custom_form_submissions');
        Schema::dropIfExists('sc_custom_form_fields');
        Schema::dropIfExists('sc_custom_forms');
    }
};
