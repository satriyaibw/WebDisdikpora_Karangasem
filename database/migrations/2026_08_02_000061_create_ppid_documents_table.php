<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 4: Dokumen informasi publik PPID (UU KIP No. 14 Tahun 2008).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppid_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('doc_number')->nullable();
            $table->year('year')->nullable();
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->foreignId('category_id')
                ->nullable()
                ->constrained('ppid_categories')
                ->nullOnDelete();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamps();

            $table->index('category_id');
            $table->index('status');
            $table->index('year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppid_documents');
    }
};
