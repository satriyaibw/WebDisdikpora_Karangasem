<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Validasi berkas PDF asli (MasterPlan 4.4).
 *
 * Selain rule `mimetypes:application/pdf`, dilakukan pemeriksaan
 * header `%PDF-` (dalam 1024 byte awal — ISO 32000-1 memperbolehkan
 * junk sebelum header) serta trailer `%%EOF` (4 KB terakhir) agar
 * berkas ber-ekstensi .pdf palsu (mis. script berbahaya, polyglot
 * `%PDF-1.4` + isi tidak sah) tetap tertolak.
 */
class ValidPdfFile implements ValidationRule
{
    /**
     * Magic bytes PDF sesuai spesifikasi ISO 32000-1.
     */
    private const PDF_MAGIC = '%PDF-';

    /**
     * Trailer wajib di akhir berkas PDF (ISO 32000-1 pasal 7.5.5).
     */
    private const PDF_TRAILER = '%%EOF';

    /**
     * Jumlah byte awal yang diperiksa untuk menemukan header `%PDF-`.
     */
    private const HEADER_SCAN_BYTES = 1024;

    /**
     * Jumlah byte akhir yang diperiksa untuk menemukan trailer `%%EOF`.
     */
    private const TRAILER_SCAN_BYTES = 4096;

    /**
     * Pesan saat berkas tidak dapat dibaca.
     */
    private const UNREADABLE_MESSAGE = 'Berkas PDF tidak dapat dibaca. Silakan unggah ulang.';

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $path = $this->resolveFilePath($value);

        if ($path === null || ! is_file($path)) {
            $fail(self::UNREADABLE_MESSAGE);

            return;
        }

        $handle = fopen($path, 'rb');

        if ($handle === false) {
            $fail(self::UNREADABLE_MESSAGE);

            return;
        }

        try {
            $head = fread($handle, self::HEADER_SCAN_BYTES);
            $tailStart = (int) max(0, filesize($path) - self::TRAILER_SCAN_BYTES);
            fseek($handle, $tailStart);
            $tail = fread($handle, self::TRAILER_SCAN_BYTES);
        } finally {
            fclose($handle);
        }

        if (! str_contains((string) $head, self::PDF_MAGIC)
            || ! str_contains((string) $tail, self::PDF_TRAILER)) {
            $fail('Berkas harus berupa PDF asli yang valid (bukan sekadar ekstensi .pdf).');
        }
    }

    /**
     * Resolusi lokasi fisik berkas dari nilai state form.
     *
     * Nilai bisa berupa `TemporaryUploadedFile` (proses validasi form)
     * atau string path yang sudah tersimpan di disk `public` (re-validasi).
     */
    private function resolveFilePath(mixed $value): ?string
    {
        if ($value instanceof TemporaryUploadedFile) {
            $realPath = $value->getRealPath();

            return $realPath !== false ? $realPath : null;
        }

        if (is_string($value) && $value !== '' && ! str_contains($value, '..')) {
            return Storage::disk('public')->path($value);
        }

        return null;
    }
}
