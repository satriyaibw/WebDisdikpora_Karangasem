<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 5: Repositori dokumen SOP per Bidang/Sub-Bagian (MasterPlan 5.2).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sop_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('sop_number')->nullable();
            $table->date('issuance_date')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('bidang_id')
                ->nullable()
                ->constrained('bidangs')
                ->nullOnDelete();
            $table->string('file_path');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamps();

            $table->index('bidang_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop_documents');
    }
};
