<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 6.6: Seksi konten halaman Profil yang dinamis.
 *
 * Mengganti field tetap `profile.vision` / `profile.mission` /
 * `profile.duties` pada tabel `settings` dengan tabel `profile_sections`
 * agar admin dapat menambah seksi baru (program prioritas, sasaran
 * program, dst.) tanpa perubahan kode.
 *
 * Data lama disalin otomatis ke tabel baru bila tersedia, lalu key lama
 * dihapus (tidak lagi dipakai portal publik).
 */
return new class extends Migration
{
    /**
     * Pemetaan key settings lama ke seksi default.
     *
     * @var array<string, array{title: string, slug: string, sort_order: int}>
     */
    private const LEGACY_MAPPING = [
        'profile.vision' => ['title' => 'Visi', 'slug' => 'visi', 'sort_order' => 0],
        'profile.mission' => ['title' => 'Misi', 'slug' => 'misi', 'sort_order' => 1],
        'profile.duties' => ['title' => 'Tugas & Fungsi', 'slug' => 'tugas-fungsi', 'sort_order' => 2],
    ];

    public function up(): void
    {
        Schema::create('profile_sections', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        $existingSlugs = DB::table('profile_sections')->pluck('slug')->all();

        foreach (self::LEGACY_MAPPING as $key => $section) {
            if (in_array($section['slug'], $existingSlugs, true)) {
                continue;
            }

            $value = DB::table('settings')->where('key', $key)->value('value');

            if ($value === null) {
                continue;
            }

            DB::table('profile_sections')->insert([
                'title' => $section['title'],
                'slug' => $section['slug'],
                'content' => $value,
                'sort_order' => $section['sort_order'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $existingSlugs[] = $section['slug'];
        }

        DB::table('settings')->whereIn('key', array_keys(self::LEGACY_MAPPING))->delete();
    }

    public function down(): void
    {
        foreach (self::LEGACY_MAPPING as $key => $section) {
            $value = DB::table('profile_sections')
                ->where('slug', $section['slug'])
                ->value('content');

            if ($value === null) {
                continue;
            }

            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                [
                    'value' => $value,
                    'group' => 'profile',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        Schema::dropIfExists('profile_sections');
    }
};
