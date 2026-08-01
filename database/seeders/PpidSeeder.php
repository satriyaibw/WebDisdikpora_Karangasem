<?php

namespace Database\Seeders;

use App\Models\PpidCategory;
use App\Models\PpidDocument;
use Database\Seeders\Traits\SeedsDummyPdfs;
use Illuminate\Database\Seeder;

/**
 * Fase 4: Seed data awal repositori dokumen PPID.
 *
 * Idempotent — aman dijalankan berulang kali (updateOrCreate /
 * firstOrCreate berdasarkan slug / judul + kategori).
 */
class PpidSeeder extends Seeder
{
    use SeedsDummyPdfs;

    /**
     * Kategori KIP sesuai MasterPlan 4.1.
     */
    private const CATEGORIES = [
        'informasi-berkala' => [
            'name' => 'Informasi Berkala',
            'description' => 'Informasi yang disediakan secara berkala setiap 6 bulan, antara lain LAKIP, RENSTRA, RENJA, DPA, dan laporan keuangan.',
        ],
        'informasi-serta-merta' => [
            'name' => 'Informasi Serta Merta',
            'description' => 'Informasi yang dapat mengancam hajat hidup orang banyak dan ketertiban umum, seperti pengumuman darurat, kondisi bencana, dan keselamatan.',
        ],
        'informasi-setiap-saat' => [
            'name' => 'Informasi Setiap Saat',
            'description' => 'Informasi yang wajib tersedia setiap saat, antara lain daftar peraturan, ringkasan program, dan daftar informasi publik.',
        ],
    ];

    /**
     * Dokumen contoh (MasterPlan 4.1) — status published.
     *
     * @var array<int, array{title: string, doc_number: string, year: int, category: string, description: string, file: string}>
     */
    private const DOCUMENTS = [
        [
            'title' => 'LAKIP Disdikpora Karangasem',
            'doc_number' => 'LAKIP/2025',
            'year' => 2025,
            'category' => 'informasi-berkala',
            'description' => 'Laporan Akuntabilitas Kinerja Instansi Pemerintah.',
            'file' => 'LAKIP.pdf',
        ],
        [
            'title' => 'RENSTRA Disdikpora Karangasem',
            'doc_number' => 'RENSTRA/2025',
            'year' => 2025,
            'category' => 'informasi-berkala',
            'description' => 'Rencana Strategis Dinas Pendidikan, Kepemudaan dan Olahraga.',
            'file' => 'RENSTRA.pdf',
        ],
        [
            'title' => 'RENJA Disdikpora Karangasem',
            'doc_number' => 'RENJA/2025',
            'year' => 2025,
            'category' => 'informasi-berkala',
            'description' => 'Rencana Kerja tahunan Dinas Pendidikan, Kepemudaan dan Olahraga.',
            'file' => 'RENJA.pdf',
        ],
        [
            'title' => 'DPA Disdikpora Karangasem',
            'doc_number' => 'DPA/2025',
            'year' => 2025,
            'category' => 'informasi-berkala',
            'description' => 'Dokumen Pelaksanaan Anggaran Dinas Pendidikan, Kepemudaan dan Olahraga.',
            'file' => 'DPA.pdf',
        ],
        [
            'title' => 'Laporan Keuangan Disdikpora Karangasem',
            'doc_number' => 'LK/2025',
            'year' => 2025,
            'category' => 'informasi-berkala',
            'description' => 'Laporan keuangan semesteran Dinas Pendidikan, Kepemudaan dan Olahraga.',
            'file' => 'Laporan-Keuangan.pdf',
        ],
        [
            'title' => 'Daftar Peraturan Disdikpora Karangasem',
            'doc_number' => 'DP/2025',
            'year' => 2025,
            'category' => 'informasi-setiap-saat',
            'description' => 'Daftar peraturan perundang-undangan yang terkait dengan Dinas Pendidikan, Kepemudaan dan Olahraga.',
            'file' => 'Daftar-Peraturan.pdf',
        ],
        [
            'title' => 'Ringkasan Program Disdikpora Karangasem',
            'doc_number' => 'RP/2025',
            'year' => 2025,
            'category' => 'informasi-setiap-saat',
            'description' => 'Ringkasan program dan kegiatan Dinas Pendidikan, Kepemudaan dan Olahraga.',
            'file' => 'Ringkasan-Program.pdf',
        ],
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $slug => $data) {
            PpidCategory::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                ]
            );
        }

        foreach (self::DOCUMENTS as $document) {
            $category = PpidCategory::where('slug', $document['category'])->firstOrFail();

            $filePath = 'ppid/'.strtolower(str_replace('-', '_', $document['category'])).'/'.$document['file'];

            PpidDocument::firstOrCreate(
                ['title' => $document['title'], 'category_id' => $category->id],
                [
                    'doc_number' => $document['doc_number'],
                    'year' => $document['year'],
                    'description' => $document['description'],
                    'file_path' => $filePath,
                    'file_size' => $this->ensureDummyPdf($filePath),
                    'category_id' => $category->id,
                    'status' => PpidDocument::STATUS_PUBLISHED,
                ]
            );
        }
    }
}
