<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 5: Katalog Layanan Publik per Bidang (MasterPlan 5.1).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('bidang_id')
                ->nullable()
                ->constrained('bidangs')
                ->nullOnDelete();
            $table->string('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->longText('requirements')->nullable();
            $table->longText('procedure')->nullable();
            $table->string('estimated_time')->nullable();
            $table->string('cost')->nullable();
            $table->string('pic_name')->nullable();
            $table->string('pic_contact')->nullable();
            $table->string('form_template')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamps();

            $table->index('bidang_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
