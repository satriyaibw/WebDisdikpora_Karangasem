<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel officials (pohon jabatan) dihapus karena digantikan
     * unggahan gambar bagan struktur organisasi di halaman Pengaturan
     * (setting profile.struktur_image).
     */
    public function up(): void
    {
        Schema::dropIfExists('officials');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('officials', function (Blueprint $table) {
            $table->id();
            $table->string('jabatan');
            $table->string('nama');
            $table->string('nip', 18)->nullable();
            $table->foreignId('parent_id')->nullable()
                ->constrained('officials')
                ->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }
};
