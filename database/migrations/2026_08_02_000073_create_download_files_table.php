<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 5: Pusat Unduhan Berkas — formulir resmi & Juknis (MasterPlan 5.3).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('download_files', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['formulir', 'juknis', 'lainnya'])->default('formulir');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamps();

            $table->index('type');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('download_files');
    }
};
