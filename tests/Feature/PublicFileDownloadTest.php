<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\DownloadFile;
use App\Models\PpidDocument;
use App\Models\Service;
use App\Models\SopDocument;
use Database\Seeders\Traits\SeedsDummyPdfs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Unduhan asli (Content-Disposition: attachment) untuk seluruh modul berkas
 * publik — issue #16 (P2-1): SOP, PPID, Unduhan, Layanan, Pengumuman.
 */
class PublicFileDownloadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    private function seededFile(string $path): void
    {
        Storage::disk('public')->put($path, 'konten pdf uji');
    }

    /** ============================= SOP ============================= */
    public function test_sop_download_returns_attachment_with_original_filename(): void
    {
        $this->seededFile('lampiran/sop/sop-legalisir-ijazah.pdf');
        $sop = SopDocument::create([
            'title' => 'SOP Pelayanan Legalisir Ijazah',
            'slug' => 'sop-legalisir-ijazah',
            'file_path' => 'lampiran/sop/sop-legalisir-ijazah.pdf',
            'file_size' => 17,
            'status' => SopDocument::STATUS_PUBLISHED,
        ]);

        $this->get(route('sop.download', $sop))
            ->assertOk()
            ->assertDownload('sop-legalisir-ijazah.pdf')
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_sop_download_of_draft_document_is_not_found(): void
    {
        $this->seededFile('lampiran/sop/sop-draft.pdf');
        SopDocument::create([
            'title' => 'SOP Draft',
            'slug' => 'sop-draft',
            'file_path' => 'lampiran/sop/sop-draft.pdf',
            'status' => SopDocument::STATUS_DRAFT,
        ]);

        $this->get(route('sop.download', 'sop-draft'))->assertNotFound();
    }

    public function test_sop_download_with_missing_file_is_not_found(): void
    {
        SopDocument::create([
            'title' => 'SOP Tanpa Berkas',
            'slug' => 'sop-tanpa-berkas',
            'file_path' => 'lampiran/sop/tidak-ada.pdf',
            'status' => SopDocument::STATUS_PUBLISHED,
        ]);

        $this->get(route('sop.download', 'sop-tanpa-berkas'))->assertNotFound();
    }

    /** ============================= PPID ============================= */
    public function test_ppid_download_returns_attachment_with_original_filename(): void
    {
        $this->seededFile('lampiran/ppid/lakip-2025.pdf');
        $document = PpidDocument::create([
            'title' => 'LAKIP 2025',
            'file_path' => 'lampiran/ppid/lakip-2025.pdf',
            'file_size' => 17,
            'status' => PpidDocument::STATUS_PUBLISHED,
        ]);

        $this->get(route('ppid.download', $document))
            ->assertOk()
            ->assertDownload('lakip-2025.pdf');
    }

    public function test_ppid_download_of_draft_document_is_not_found(): void
    {
        $this->seededFile('lampiran/ppid/draft.pdf');
        $document = PpidDocument::create([
            'title' => 'Dokumen Draft',
            'file_path' => 'lampiran/ppid/draft.pdf',
            'status' => PpidDocument::STATUS_DRAFT,
        ]);

        $this->get(route('ppid.download', $document))->assertNotFound();
    }

    /** ============================= Unduhan ============================= */
    public function test_unduhan_download_returns_attachment_with_original_filename(): void
    {
        $this->seededFile('lampiran/unduhan/formulir-pdb.pdf');
        $file = DownloadFile::create([
            'title' => 'Formulir Pendaftaran Peserta Didik Baru',
            'slug' => 'formulir-pdb',
            'file_path' => 'lampiran/unduhan/formulir-pdb.pdf',
            'file_size' => 17,
            'status' => DownloadFile::STATUS_PUBLISHED,
        ]);

        $this->get(route('unduhan.download', $file))
            ->assertOk()
            ->assertDownload('formulir-pdb.pdf');
    }

    public function test_unduhan_download_of_draft_file_is_not_found(): void
    {
        $this->seededFile('lampiran/unduhan/draft.pdf');
        $file = DownloadFile::create([
            'title' => 'Berkas Draft',
            'slug' => 'berkas-draft',
            'file_path' => 'lampiran/unduhan/draft.pdf',
            'status' => DownloadFile::STATUS_DRAFT,
        ]);

        $this->get(route('unduhan.download', $file))->assertNotFound();
    }

    /** ============================= Layanan (template formulir) ============================= */
    public function test_layanan_form_download_returns_attachment(): void
    {
        $this->seededFile('lampiran/layanan/form-mutasi.pdf');
        $service = Service::create([
            'name' => 'Mutasi Siswa',
            'slug' => 'mutasi-siswa',
            'form_template' => 'lampiran/layanan/form-mutasi.pdf',
            'status' => Service::STATUS_PUBLISHED,
        ]);

        $this->get(route('layanan.unduh-formulir', $service))
            ->assertOk()
            ->assertDownload('form-mutasi.pdf');
    }

    public function test_layanan_form_download_of_draft_service_is_not_found(): void
    {
        $this->seededFile('lampiran/layanan/form-draft.pdf');
        $service = Service::create([
            'name' => 'Layanan Draft',
            'slug' => 'layanan-draft',
            'form_template' => 'lampiran/layanan/form-draft.pdf',
            'status' => Service::STATUS_DRAFT,
        ]);

        $this->get(route('layanan.unduh-formulir', $service))->assertNotFound();
    }

    /** ============================= Pengumuman (lampiran) ============================= */
    public function test_pengumuman_attachment_download_returns_attachment(): void
    {
        $this->seededFile('lampiran/pengumuman/lampiran-1.pdf');
        $announcement = Announcement::create([
            'title' => 'Pengumuman Resmi',
            'content' => '<p>Isi pengumuman.</p>',
            'attachment_path' => 'lampiran/pengumuman/lampiran-1.pdf',
            'status' => Announcement::STATUS_PUBLISHED,
        ]);

        $this->get(route('pengumuman.unduh-lampiran', $announcement))
            ->assertOk()
            ->assertDownload('lampiran-1.pdf');
    }

    public function test_pengumuman_attachment_download_of_draft_is_not_found(): void
    {
        $this->seededFile('lampiran/pengumuman/lampiran-draft.pdf');
        $announcement = Announcement::create([
            'title' => 'Pengumuman Draft',
            'content' => '<p>Isi pengumuman.</p>',
            'attachment_path' => 'lampiran/pengumuman/lampiran-draft.pdf',
            'status' => Announcement::STATUS_DRAFT,
        ]);

        $this->get(route('pengumuman.unduh-lampiran', $announcement))->assertNotFound();
    }

    /** ============================= PDF dummy seeder ============================= */
    public function test_ensure_dummy_pdf_writes_valid_pdf_with_text(): void
    {
        $trait = $this->dummyPdfTrait();

        $size = $trait->make('lampiran/sop/uji.pdf');

        $this->assertGreaterThan(500, $size);

        $content = Storage::disk('public')->get('lampiran/sop/uji.pdf');
        $this->assertStringStartsWith('%PDF-', $content);
        $this->assertStringContainsString('Dokumen Contoh - Digenerated dari Seeder', $content);
        $this->assertStringContainsString('startxref', $content);
    }

    public function test_ensure_dummy_pdf_replaces_corrupt_legacy_file(): void
    {
        Storage::disk('public')->put('lampiran/unduhan/korup.pdf', "%PDF-1.4\n1 0 obj\nendobj\n%%EOF\n");
        $trait = $this->dummyPdfTrait();

        $size = $trait->make('lampiran/unduhan/korup.pdf');

        $this->assertGreaterThan(500, $size);
        $this->assertStringStartsWith('%PDF-', Storage::disk('public')->get('lampiran/unduhan/korup.pdf'));
    }

    public function test_ensure_dummy_pdf_is_idempotent(): void
    {
        $trait = $this->dummyPdfTrait();
        $first = $trait->make('lampiran/ppid/sama.pdf');
        $second = $trait->make('lampiran/ppid/sama.pdf');

        $this->assertSame($first, $second);
    }

    private function dummyPdfTrait(): object
    {
        return new class
        {
            use SeedsDummyPdfs;

            public function make(string $path): int
            {
                return $this->ensureDummyPdf($path);
            }
        };
    }
}
