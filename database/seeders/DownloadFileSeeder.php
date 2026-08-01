<?php

namespace Database\Seeders;

use App\Models\DownloadFile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Fase 5: Seed data awal Pusat Unduhan Berkas (MasterPlan 5.3).
 *
 * Idempotent — aman dijalankan berulang kali (updateOrCreate berdasarkan judul).
 * Berkas PDF dummy dibuat di disk `public` (pola PpidSeeder).
 */
class DownloadFileSeeder extends Seeder
{
    /**
     * @var array<int, array<string, mixed>>
     */
    public const FILES = [
        [
            'title' => 'Formulir Pendaftaran Peserta Didik Baru',
            'description' => 'Formulir resmi pendaftaran peserta didik baru jenjang SD/SMP.',
            'type' => 'formulir',
        ],
        [
            'title' => 'Formulir Pengajuan Mutasi Siswa',
            'description' => 'Formulir pengajuan perpindahan peserta didik antar sekolah.',
            'type' => 'formulir',
        ],
        [
            'title' => 'Juknis Bantuan Operasional Sekolah (BOS)',
            'description' => 'Petunjuk teknis pengelolaan dana Bantuan Operasional Sekolah.',
            'type' => 'juknis',
        ],
    ];

    public function run(): void
    {
        foreach (self::FILES as $file) {
            $filePath = 'lampiran/unduhan/'.Str::slug($file['title']).'.pdf';

            DownloadFile::updateOrCreate(
                ['title' => $file['title']],
                [
                    'description' => $file['description'],
                    'type' => $file['type'],
                    'file_path' => $filePath,
                    'file_size' => $this->ensureDummyPdf($filePath),
                    'status' => DownloadFile::STATUS_PUBLISHED,
                ]
            );
        }
    }

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
