<?php

namespace Database\Seeders\Traits;

use Illuminate\Support\Facades\Storage;

/**
 * Pastikan berkas PDF dummy tersedia di disk `public` saat seeding.
 *
 * Idempotent — file hanya dibuat bila belum ada, mengembalikan ukurannya.
 */
trait SeedsDummyPdfs
{
    /**
     * Pastikan file PDF dummy tersedia di disk `public` dan kembalikan ukurannya.
     */
    private function ensureDummyPdf(string $path): int
    {
        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            $content = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] >>\nendobj\nxref\n0 4\ntrailer\n<< /Size 4 /Root 1 0 R >>\n%%EOF\n";

            $disk->put($path, $content);
        }

        return $disk->size($path) ?? 0;
    }
}
