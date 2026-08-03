<?php

namespace Database\Seeders\Traits;

use Illuminate\Support\Facades\Storage;

/**
 * Pastikan berkas PDF dummy tersedia di disk `public` saat seeding.
 *
 * PDF yang dihasilkan adalah dokumen valid satu halaman berisi teks
 * (bukan string minimal tanpa konten), sehingga pratinjau data hasil
 * seeding tampil normal. Offset tabel `xref` dihitung saat runtime
 * agar berkas selalu valid secara struktural.
 *
 * Idempotent — berkas hanya ditulis bila belum ada atau lebih kecil
 * dari PDF valid terbaru (healing berkas lama yang korup).
 */
trait SeedsDummyPdfs
{
    /**
     * Ukuran minimum berkas yang dianggap PDF valid (byte).
     * PDF dummy baru berukuran ±800 byte; berkas lama yang rusak ±235 byte.
     */
    private const MIN_VALID_DUMMY_PDF_SIZE = 500;

    /**
     * Pastikan file PDF dummy tersedia di disk `public` dan kembalikan ukurannya.
     */
    private function ensureDummyPdf(string $path): int
    {
        $disk = Storage::disk('public');

        if ($disk->exists($path) && $disk->size($path) >= self::MIN_VALID_DUMMY_PDF_SIZE) {
            return $disk->size($path) ?? 0;
        }

        $disk->put($path, $this->buildDummyPdf());

        return $disk->size($path) ?? 0;
    }

    /**
     * Bangun PDF valid minimal: satu halaman A4/US Letter berisi satu
     * baris teks Helvetica, dengan offset objek dihitung dinamis.
     */
    private function buildDummyPdf(): string
    {
        $text = 'Dokumen Contoh - Digenerated dari Seeder';
        $stream = "BT\n/F1 14 Tf\n72 720 Td\n($text) Tj\nET";

        $objects = [
            "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj",
            "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj",
            "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj",
            "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj",
            "5 0 obj\n<< /Length ".strlen($stream)." >>\nstream\n{$stream}\nendstream\nendobj",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object."\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }
        $pdf .= "trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF\n";

        return $pdf;
    }
}
