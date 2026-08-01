<?php

namespace Database\Seeders;

use App\Models\Bidang;
use App\Models\SopDocument;
use Database\Seeders\Traits\SeedsDummyPdfs;
use Illuminate\Database\Seeder;

/**
 * Fase 5: Seed data awal Repositori Dokumen SOP (MasterPlan 5.2).
 *
 * Idempotent — aman dijalankan berulang kali (updateOrCreate berdasarkan
 * slug, bukan judul, sehingga perubahan judul tidak membuat duplikat).
 * Berkas PDF dummy dibuat di disk `public` (pola PpidSeeder).
 */
class SopSeeder extends Seeder
{
    use SeedsDummyPdfs;

    /**
     * @var array<int, array<string, mixed>>
     */
    public const DOCUMENTS = [
        [
            'slug' => 'sop-legalisir-ijazah',
            'title' => 'SOP Pelayanan Legalisir Ijazah',
            'sop_number' => '800/001/SOP/2025',
            'issuance_date' => '2025-01-15',
            'bidang' => 'sekretariat',
            'description' => 'Standar operasional prosedur pelayanan legalisir ijazah dan dokumen akademik.',
        ],
        [
            'slug' => 'sop-mutasi-peserta-didik',
            'title' => 'SOP Penanganan Mutasi Peserta Didik',
            'sop_number' => '800/002/SOP/2025',
            'issuance_date' => '2025-02-10',
            'bidang' => 'pembinaan-pendidikan-smp',
            'description' => 'Standar operasional prosedur pelayanan mutasi peserta didik antar sekolah.',
        ],
        [
            'slug' => 'sop-verifikasi-paud-bop',
            'title' => 'SOP Verifikasi Lembaga PAUD Penerima BOP',
            'sop_number' => '800/003/SOP/2025',
            'issuance_date' => '2025-03-05',
            'bidang' => 'pembinaan-pendidikan-paud-pnf',
            'description' => 'Standar operasional prosedur verifikasi lembaga PAUD calon penerima bantuan operasional.',
        ],
        [
            'slug' => 'sop-rekomendasi-kegiatan-kepemudaan',
            'title' => 'SOP Penerbitan Rekomendasi Kegiatan Kepemudaan',
            'sop_number' => '800/004/SOP/2025',
            'issuance_date' => '2025-04-20',
            'bidang' => 'pemuda-olahraga',
            'description' => 'Standar operasional prosedur penerbitan rekomendasi kegiatan organisasi kepemudaan.',
        ],
    ];

    public function run(): void
    {
        foreach (self::DOCUMENTS as $document) {
            $bidang = Bidang::where('slug', $document['bidang'])->first();

            $filePath = 'lampiran/sop/'.$document['slug'].'.pdf';

            SopDocument::updateOrCreate(
                ['slug' => $document['slug']],
                [
                    'title' => $document['title'],
                    'sop_number' => $document['sop_number'],
                    'issuance_date' => $document['issuance_date'],
                    'bidang_id' => $bidang?->id,
                    'description' => $document['description'],
                    'file_path' => $filePath,
                    'file_size' => $this->ensureDummyPdf($filePath),
                    'status' => SopDocument::STATUS_PUBLISHED,
                ]
            );
        }
    }
}
