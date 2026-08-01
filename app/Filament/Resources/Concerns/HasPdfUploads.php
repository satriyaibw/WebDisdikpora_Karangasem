<?php

namespace App\Filament\Resources\Concerns;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Helper bersama untuk resource ber-upload PDF (PPID, Layanan, SOP, Unduhan).
 *
 * Menyediakan penamaan file aman (ekstensi dipaksa `.pdf`) dan pembacaan
 * ukuran berkas dari disk `public`.
 */
trait HasPdfUploads
{
    /**
     * Ukuran berkas (byte) dari path di disk `public`, atau null bila tidak terbaca.
     */
    public static function resolveStoredFileSize(string $path): ?int
    {
        try {
            $size = Storage::disk('public')->size($path);
        } catch (\Throwable) {
            return null;
        }

        return $size === false ? null : $size;
    }

    /**
     * Nama file aman untuk dokumen PDF.
     *
     * Nama asli dipertahankan (dibersihkan dari segmen path & karakter
     * berbahaya) lalu diberi suffix acak agar unik di disk. Ekstensi
     * SELALU dipaksa `.pdf` — ekstensi dari client tidak dipercaya agar
     * file polyglot ber-ekstensi berbahaya (mis. `.php`) tidak dapat
     * dieksekusi web server.
     */
    public static function safeStoredFileName(string $originalName): string
    {
        $safeName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
        $safeName = $safeName !== '' ? $safeName : 'dokumen';

        return Str::limit($safeName, 60, '').'-'.Str::lower(Str::random(8)).'.pdf';
    }
}
