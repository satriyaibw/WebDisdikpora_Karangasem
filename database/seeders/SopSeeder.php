<?php

namespace Database\Seeders;

use App\Models\Bidang;
use App\Models\SopDocument;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Fase 5: Seed data awal Repositori Dokumen SOP (MasterPlan 5.2).
 *
 * Idempotent — aman dijalankan berulang kali (updateOrCreate berdasarkan judul).
 * Berkas PDF dummy dibuat di disk `public` (pola PpidSeeder).
 */
class SopSeeder extends Seeder
{
    /**
     * @var array<int, array<string, mixed>>
     */
    public const DOCUMENTS = [
        [
            'title' => 'SOP Pelayanan Legalisir Ijazah',
            'sop_number' => '800/001/SOP/2025',
            'issuance_date' => '2025-01-15',
            'bidang' => 'sekretariat',
            'description' => 'Standar operasional prosedur pelayanan legalisir ijazah dan dokumen akademik.',
        ],
        [
            'title' => 'SOP Penanganan Mutasi Peserta Didik',
            'sop_number' => '800/002/SOP/2025',
            'issuance_date' => '2025-02-10',
            'bidang' => 'pembinaan-pendidikan-smp',
            'description' => 'Standar operasional prosedur pelayanan mutasi peserta didik antar sekolah.',
        ],
        [
            'title' => 'SOP Verifikasi Lembaga PAUD Penerima BOP',
            'sop_number' => '800/003/SOP/2025',
            'issuance_date' => '2025-03-05',
            'bidang' => 'pembinaan-pendidikan-paud-pnf',
            'description' => 'Standar operasional prosedur verifikasi lembaga PAUD calon penerima bantuan operasional.',
        ],
        [
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

            $filePath = 'lampiran/sop/'.Str::slug($document['title']).'.pdf';

            SopDocument::updateOrCreate(
                ['title' => $document['title']],
                [
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
