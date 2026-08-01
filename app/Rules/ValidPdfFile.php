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
 * magic bytes `%PDF-` pada bagian awal berkas agar file ber-ekstensi
 * PDF palsu (mis. script berbahaya) tetap tertolak.
 */
class ValidPdfFile implements ValidationRule
{
    /**
     * Magic bytes PDF sesuai spesifikasi ISO 32000-1.
     */
    private const PDF_MAGIC = '%PDF-';

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $path = $this->resolveFilePath($value);

        if ($path === null || ! is_file($path)) {
            $fail('Berkas PDF tidak dapat dibaca. Silakan unggah ulang.');

            return;
        }

        $handle = fopen($path, 'rb');

        if ($handle === false) {
            $fail('Berkas PDF tidak dapat dibaca. Silakan unggah ulang.');

            return;
        }

        $header = fread($handle, strlen(self::PDF_MAGIC));
        fclose($handle);

        if ($header !== self::PDF_MAGIC) {
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
