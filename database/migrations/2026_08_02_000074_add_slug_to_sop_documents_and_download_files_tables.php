<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Fase 5: kolom `slug` unik pada sop_documents & download_files.
 *
 * Digunakan sebagai identitas stabil untuk seeder (tahan terhadap
 * perubahan judul) dan URL publik pada Fase 6.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sop_documents', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('title');
        });

        Schema::table('download_files', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('title');
        });

        foreach (['sop_documents', 'download_files'] as $table) {
            $this->backfillSlugs($table);
        }

        Schema::table('sop_documents', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
            $table->unique('slug');
        });

        Schema::table('download_files', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('sop_documents', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });

        Schema::table('download_files', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }

    /**
     * Isi `slug` dari judul untuk seluruh baris, dipastikan unik.
     */
    private function backfillSlugs(string $table): void
    {
        DB::table($table)->orderBy('id')->each(function (object $row) use ($table): void {
            $base = Str::slug((string) $row->title);
            $base = $base !== '' ? $base : Str::lower(Str::random(8));
            $slug = $base;

            for ($i = 2; DB::table($table)->where('slug', $slug)->exists(); $i++) {
                $slug = $base.'-'.$i;
            }

            DB::table($table)->where('id', $row->id)->update(['slug' => $slug]);
        });
    }
};
