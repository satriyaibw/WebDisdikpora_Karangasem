<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 6.5: Kategori berkas unduhan dinamis (MasterPlan 5.3).
 *
 * Mengganti kolom `type` (enum statis: formulir/juknis/lainnya) dengan
 * tabel `download_categories` agar admin dapat menambah kategori tanpa
 * perubahan kode. Data lama dipetakan ke 3 kategori bawaan.
 */
return new class extends Migration
{
    /**
     * Kategori bawaan beserta label publiknya.
     *
     * @var array<int, array{slug: string, name: string}>
     */
    private const DEFAULT_CATEGORIES = [
        ['slug' => 'formulir', 'name' => 'Formulir'],
        ['slug' => 'juknis', 'name' => 'Petunjuk Teknis (Juknis)'],
        ['slug' => 'lainnya', 'name' => 'Lainnya'],
    ];

    public function up(): void
    {
        Schema::create('download_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('sort_order');
        });

        Schema::table('download_files', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()
                ->after('type')
                ->constrained('download_categories')
                ->nullOnDelete();
        });

        $categoryIds = [];
        foreach (self::DEFAULT_CATEGORIES as $index => $category) {
            $categoryIds[$category['slug']] = DB::table('download_categories')->insertGetId([
                'name' => $category['name'],
                'slug' => $category['slug'],
                'sort_order' => $index,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('download_files')->get(['id', 'type'])->each(function ($file) use ($categoryIds): void {
            DB::table('download_files')
                ->where('id', $file->id)
                ->update(['category_id' => $categoryIds[$file->type] ?? $categoryIds['lainnya']]);
        });

        Schema::table('download_files', function (Blueprint $table) {
            $table->dropIndex(['type']);
        });

        Schema::table('download_files', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('download_files', function (Blueprint $table) {
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::table('download_files', function (Blueprint $table) {
            $table->dropIndex(['category_id']);
            $table->enum('type', ['formulir', 'juknis', 'lainnya'])->default('formulir');
        });

        $categoryBySlug = DB::table('download_categories')->get(['id', 'slug'])
            ->pluck('slug', 'id')
            ->all();

        DB::table('download_files')->get(['id', 'category_id'])->each(function ($file) use ($categoryBySlug): void {
            $type = $categoryBySlug[$file->category_id] ?? 'lainnya';
            $type = in_array($type, ['formulir', 'juknis', 'lainnya'], true) ? $type : 'lainnya';

            DB::table('download_files')
                ->where('id', $file->id)
                ->update(['type' => $type]);
        });

        Schema::table('download_files', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });

        Schema::table('download_files', function (Blueprint $table) {
            $table->index('type');
        });

        Schema::dropIfExists('download_categories');
    }
};
