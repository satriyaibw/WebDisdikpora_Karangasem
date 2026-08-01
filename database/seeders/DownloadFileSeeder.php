<?php

namespace Database\Seeders;

use App\Models\DownloadFile;
use Database\Seeders\Traits\SeedsDummyPdfs;
use Illuminate\Database\Seeder;

/**
 * Fase 5: Seed data awal Pusat Unduhan Berkas (MasterPlan 5.3).
 *
 * Idempotent — aman dijalankan berulang kali (updateOrCreate berdasarkan
 * slug, bukan judul, sehingga perubahan judul tidak membuat duplikat).
 * Berkas PDF dummy dibuat di disk `public` (pola PpidSeeder).
 */
class DownloadFileSeeder extends Seeder
{
    use SeedsDummyPdfs;

    /**
     * @var array<int, array<string, mixed>>
     */
    public const FILES = [
        [
            'slug' => 'formulir-pendaftaran-peserta-didik-baru',
            'title' => 'Formulir Pendaftaran Peserta Didik Baru',
            'description' => 'Formulir resmi pendaftaran peserta didik baru jenjang SD/SMP.',
            'type' => 'formulir',
        ],
        [
            'slug' => 'formulir-pengajuan-mutasi-siswa',
            'title' => 'Formulir Pengajuan Mutasi Siswa',
            'description' => 'Formulir pengajuan perpindahan peserta didik antar sekolah.',
            'type' => 'formulir',
        ],
        [
            'slug' => 'juknis-bantuan-operasional-sekolah',
            'title' => 'Juknis Bantuan Operasional Sekolah (BOS)',
            'description' => 'Petunjuk teknis pengelolaan dana Bantuan Operasional Sekolah.',
            'type' => 'juknis',
        ],
    ];

    public function run(): void
    {
        foreach (self::FILES as $file) {
            $filePath = 'lampiran/unduhan/'.$file['slug'].'.pdf';

            DownloadFile::updateOrCreate(
                ['slug' => $file['slug']],
                [
                    'title' => $file['title'],
                    'description' => $file['description'],
                    'type' => $file['type'],
                    'file_path' => $filePath,
                    'file_size' => $this->ensureDummyPdf($filePath),
                    'status' => DownloadFile::STATUS_PUBLISHED,
                ]
            );
        }
    }
}
