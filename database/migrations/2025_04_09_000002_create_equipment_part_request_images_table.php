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
        Schema::create('equipment_part_request_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('equipment_part_requests')->onDelete('cascade');
            $table->string('image_path');
            $table->string('original_filename');
            $table->integer('file_size')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_part_request_images');
    }
};
