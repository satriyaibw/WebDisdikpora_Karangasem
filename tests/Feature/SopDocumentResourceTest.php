<?php

namespace Tests\Feature;

use App\Filament\Resources\SopDocumentResource;
use App\Filament\Resources\SopDocumentResource\Pages\CreateSopDocument;
use App\Filament\Resources\SopDocumentResource\Pages\EditSopDocument;
use App\Filament\Resources\SopDocumentResource\Pages\ListSopDocuments;
use App\Models\Bidang;
use App\Models\SopDocument;
use App\Models\User;
use Database\Seeders\SopSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SopDocumentResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        Storage::fake('public');
    }

    public function test_creating_sop_document_via_form_persists_record_and_audit_log(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $bidang = Bidang::where('slug', 'sekretariat')->firstOrFail();

        Livewire::test(CreateSopDocument::class)
            ->fillForm([
                'title' => 'SOP Uji Coba',
                'slug' => 'sop-uji-coba',
                'sop_number' => '800/010/SOP/2025',
                'issuance_date' => '2025-05-01',
                'bidang_id' => $bidang->id,
                'description' => 'SOP untuk pengujian.',
                'status' => SopDocument::STATUS_PUBLISHED,
                'file_path' => UploadedFile::fake()->createWithContent('sop-uji.pdf', "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF"),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $sop = SopDocument::where('title', 'SOP Uji Coba')->firstOrFail();

        $this->assertEquals($bidang->id, $sop->bidang_id);
        $this->assertEquals(SopDocument::STATUS_PUBLISHED, $sop->status);
        $this->assertTrue(Storage::disk('public')->exists($sop->file_path));
        $this->assertStringEndsWith('.pdf', $sop->file_path);

        $storedSize = Storage::disk('public')->size($sop->file_path);
        $this->assertEquals($storedSize, $sop->file_size);

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => SopDocument::class,
            'model_id' => $sop->id,
            'action' => 'create',
            'user_id' => $admin->id,
        ]);
    }

    public function test_sop_file_is_required(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateSopDocument::class)
            ->fillForm([
                'title' => 'SOP Tanpa Berkas',
                'status' => SopDocument::STATUS_DRAFT,
            ])
            ->call('create')
            ->assertHasFormErrors(['file_path' => 'required']);
    }

    public function test_sop_rejects_non_pdf_file(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateSopDocument::class)
            ->fillForm([
                'title' => 'SOP Salah Format',
                'status' => SopDocument::STATUS_DRAFT,
                'file_path' => UploadedFile::fake()->create('sop.txt', 100),
            ])
            ->call('create')
            ->assertHasFormErrors(['file_path']);
    }

    public function test_sop_rejects_pdf_with_wrong_magic_bytes(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateSopDocument::class)
            ->fillForm([
                'title' => 'SOP PDF Palsu',
                'status' => SopDocument::STATUS_DRAFT,
                'file_path' => UploadedFile::fake()->createWithContent('palsu.pdf', 'Ini bukan PDF sama sekali.'),
            ])
            ->call('create')
            ->assertHasFormErrors(['file_path']);
    }

    public function test_sop_rejects_pdf_larger_than_10mb(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $oversized = "%PDF-1.4\n".str_repeat('x', 11 * 1024 * 1024);

        Livewire::test(CreateSopDocument::class)
            ->fillForm([
                'title' => 'SOP PDF Besar',
                'status' => SopDocument::STATUS_DRAFT,
                'file_path' => UploadedFile::fake()->createWithContent('besar.pdf', $oversized),
            ])
            ->call('create')
            ->assertHasFormErrors(['file_path']);
    }

    public function test_sop_filename_is_sanitized_against_path_traversal(): void
    {
        $name = SopDocumentResource::safeStoredFileName('../../evil.pdf');

        $this->assertStringNotContainsString('/', $name);
        $this->assertStringNotContainsString('..', $name);
        $this->assertStringEndsWith('.pdf', $name);

        foreach (['evil.pdf.php', 'laporan.exe', 'skrip.php', 'dokumen'] as $originalName) {
            $stored = SopDocumentResource::safeStoredFileName($originalName);

            $this->assertStringEndsWith('.pdf', $stored, "Nama asli '{$originalName}' harus berakhiran .pdf.");
            $this->assertStringNotContainsString('.php', $stored);
            $this->assertStringNotContainsString('.exe', $stored);
            $this->assertSame(1, substr_count($stored, '.'));
        }
    }

    public function test_sop_status_transitions_are_saved(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateSopDocument::class)
            ->fillForm([
                'title' => 'SOP Status',
                'slug' => 'sop-status',
                'status' => SopDocument::STATUS_DRAFT,
                'file_path' => UploadedFile::fake()->createWithContent('status.pdf', "%PDF-1.4\n%%EOF"),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $sop = SopDocument::where('title', 'SOP Status')->firstOrFail();
        $this->assertEquals(SopDocument::STATUS_DRAFT, $sop->status);

        Livewire::test(EditSopDocument::class, ['record' => $sop->getRouteKey()])
            ->fillForm(['status' => SopDocument::STATUS_PUBLISHED])
            ->call('save')
            ->assertHasNoFormErrors();

        $sop->refresh();
        $this->assertEquals(SopDocument::STATUS_PUBLISHED, $sop->status);
    }

    public function test_sop_can_be_filtered_by_bidang(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $bidang = Bidang::where('slug', 'sekretariat')->firstOrFail();

        Livewire::test(ListSopDocuments::class)
            ->filterTable('bidang_id', $bidang->id)
            ->assertCanSeeTableRecords(
                SopDocument::where('bidang_id', $bidang->id)->get()
            );
    }

    public function test_ppid_role_can_manage_sop_documents(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin_ppid_sop');
        $this->actingAs($user);

        $this->assertTrue(SopDocumentResource::canViewAny());
        $this->assertTrue(SopDocumentResource::canCreate());
        $this->assertTrue(SopDocumentResource::canEdit(SopDocument::first()));
    }

    public function test_sop_slug_is_required_and_unique(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $existing = SopDocument::firstOrFail();

        Livewire::test(CreateSopDocument::class)
            ->fillForm([
                'title' => 'SOP Tanpa Slug',
                'slug' => '',
                'status' => SopDocument::STATUS_DRAFT,
                'file_path' => UploadedFile::fake()->createWithContent('slug-kosong.pdf', "%PDF-1.4\n%%EOF"),
            ])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'required']);

        Livewire::test(CreateSopDocument::class)
            ->fillForm([
                'title' => 'SOP Slug Duplikat',
                'slug' => $existing->slug,
                'status' => SopDocument::STATUS_DRAFT,
                'file_path' => UploadedFile::fake()->createWithContent('slug-duplikat.pdf', "%PDF-1.4\n%%EOF"),
            ])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'unique']);
    }

    public function test_sop_slug_must_be_alpha_dash(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateSopDocument::class)
            ->fillForm([
                'title' => 'SOP Slug Salah Format',
                'slug' => 'SOP Slug Salah / X',
                'status' => SopDocument::STATUS_DRAFT,
                'file_path' => UploadedFile::fake()->createWithContent('slug-salah.pdf', "%PDF-1.4\n%%EOF"),
            ])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'alpha_dash']);
    }

    public function test_download_action_is_disabled_when_file_is_missing(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $sop = SopDocument::create([
            'title' => 'SOP Tanpa Berkas',
            'slug' => 'sop-tanpa-berkas',
            'status' => SopDocument::STATUS_DRAFT,
            'file_path' => 'lampiran/sop/hilang.pdf',
        ]);

        Livewire::test(ListSopDocuments::class)
            ->assertTableActionDisabled('download', $sop);
    }

    public function test_sop_seeder_does_not_duplicate_when_title_is_renamed(): void
    {
        $this->seed(SopSeeder::class);

        $sop = SopDocument::where('slug', 'sop-legalisir-ijazah')->firstOrFail();
        $oldPath = $sop->file_path;

        $sop->update(['title' => 'SOP Baru Legalisir Ijazah']);

        $this->seed(SopSeeder::class);

        $this->assertSame(1, SopDocument::where('slug', 'sop-legalisir-ijazah')->count());
        $sop->refresh();

        $this->assertSame('SOP Pelayanan Legalisir Ijazah', $sop->title);
        $this->assertSame($oldPath, $sop->file_path);
        $this->assertTrue(Storage::disk('public')->exists($oldPath), 'Berkas seeder harus dipakai ulang, bukan dibuat duplikat.');
    }

    public function test_user_without_sop_permission_cannot_access_resource(): void
    {
        $redaksi = User::factory()->create();
        $redaksi->assignRole('admin_redaksi_berita');
        $this->actingAs($redaksi);

        $this->assertFalse(SopDocumentResource::canViewAny());
        $this->assertFalse(SopDocumentResource::canCreate());

        $this->get('/admin/sop-documents')->assertForbidden();
    }

    private function getSeededAdmin(): User
    {
        return User::where('email', 'admin@disdikpora.karangasemkab.go.id')->firstOrFail();
    }
}
