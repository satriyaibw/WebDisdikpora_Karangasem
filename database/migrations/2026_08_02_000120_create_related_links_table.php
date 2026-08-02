<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tautan terkait pada footer portal publik (mis. SP4N-LAPOR!, JDIH,
     * situs instansi lain) yang dikelola admin agar mengikuti ketentuan
     * penautan antar situs pemerintah. Daftar ditampilkan pada kolom
     * "Tautan Terkait" di footer.
     */
    public function up(): void
    {
        Schema::create('related_links', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('related_links');
    }
};
