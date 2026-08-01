<?php

namespace Tests\Feature;

use App\Filament\Resources\SopDocumentResource;
use App\Filament\Resources\SopDocumentResource\Pages\CreateSopDocument;
use App\Filament\Resources\SopDocumentResource\Pages\EditSopDocument;
use App\Filament\Resources\SopDocumentResource\Pages\ListSopDocuments;
use App\Models\Bidang;
use App\Models\SopDocument;
use App\Models\User;
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
