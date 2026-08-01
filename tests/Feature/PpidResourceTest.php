<?php

namespace Tests\Feature;

use App\Filament\Resources\PpidCategoryResource;
use App\Filament\Resources\PpidCategoryResource\Pages\CreatePpidCategory;
use App\Filament\Resources\PpidDocumentResource;
use App\Filament\Resources\PpidDocumentResource\Pages\CreatePpidDocument;
use App\Filament\Resources\PpidDocumentResource\Pages\EditPpidDocument;
use App\Models\PpidCategory;
use App\Models\PpidDocument;
use App\Models\User;
use App\Rules\ValidPdfFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Livewire\Livewire;
use Tests\TestCase;

class PpidResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        Storage::fake('public');
    }

    public function test_creating_ppid_category_via_form_persists_record_and_audit_log(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreatePpidCategory::class)
            ->fillForm([
                'name' => 'Informasi Uji',
                'slug' => 'informasi-uji',
                'description' => 'Kategori untuk pengujian.',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $category = PpidCategory::where('slug', 'informasi-uji')->firstOrFail();
        $this->assertEquals('Informasi Uji', $category->name);

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => PpidCategory::class,
            'model_id' => $category->id,
            'action' => 'create',
            'user_id' => $admin->id,
        ]);
    }

    public function test_creating_ppid_document_via_form_persists_file_size_and_audit_log(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $category = PpidCategory::where('slug', 'informasi-berkala')->firstOrFail();

        Livewire::test(CreatePpidDocument::class)
            ->fillForm([
                'title' => 'Dokumen Uji',
                'doc_number' => '800/001/PPID',
                'year' => 2025,
                'description' => 'Deskripsi uji',
                'category_id' => $category->id,
                'status' => PpidDocument::STATUS_PUBLISHED,
                'file_path' => UploadedFile::fake()->createWithContent('dokumen-uji.pdf', "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF"),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $document = PpidDocument::where('title', 'Dokumen Uji')->firstOrFail();

        $this->assertEquals($category->id, $document->category_id);
        $this->assertEquals(PpidDocument::STATUS_PUBLISHED, $document->status);
        $this->assertEquals(2025, $document->year);
        $this->assertTrue(Storage::disk('public')->exists($document->file_path));
        $this->assertStringEndsWith('.pdf', $document->file_path);

        $storedSize = Storage::disk('public')->size($document->file_path);
        $this->assertEquals($storedSize, $document->file_size);

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => PpidDocument::class,
            'model_id' => $document->id,
            'action' => 'create',
            'user_id' => $admin->id,
        ]);
    }

    public function test_valid_pdf_is_accepted_and_stored(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $category = PpidCategory::where('slug', 'informasi-setiap-saat')->firstOrFail();

        Livewire::test(CreatePpidDocument::class)
            ->fillForm([
                'title' => 'PDF Valid',
                'category_id' => $category->id,
                'status' => PpidDocument::STATUS_DRAFT,
                'file_path' => UploadedFile::fake()->createWithContent('valid.pdf', "%PDF-1.4\n1 0 obj\n%%EOF"),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $document = PpidDocument::where('title', 'PDF Valid')->firstOrFail();

        $this->assertNotNull($document->file_path);
        $this->assertTrue(Storage::disk('public')->exists($document->file_path));
    }

    public function test_non_pdf_file_is_rejected(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $category = PpidCategory::where('slug', 'informasi-berkala')->firstOrFail();

        Livewire::test(CreatePpidDocument::class)
            ->fillForm([
                'title' => 'Bukan PDF',
                'category_id' => $category->id,
                'status' => PpidDocument::STATUS_DRAFT,
                'file_path' => UploadedFile::fake()->create('dokumen.txt', 100),
            ])
            ->call('create')
            ->assertHasFormErrors(['file_path']);
    }

    public function test_file_with_pdf_extension_but_wrong_magic_bytes_is_rejected(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $category = PpidCategory::where('slug', 'informasi-berkala')->firstOrFail();

        Storage::disk('public')->put('ppid/palsu.pdf', 'Ini bukan PDF sama sekali.');

        $validator = Validator::make(
            ['file' => 'ppid/palsu.pdf'],
            ['file' => [new ValidPdfFile]]
        );

        $this->assertTrue($validator->fails());
        $this->assertStringContainsString('PDF asli', $validator->errors()->first('file'));

        Livewire::test(CreatePpidDocument::class)
            ->fillForm([
                'title' => 'PDF Palsu',
                'category_id' => $category->id,
                'status' => PpidDocument::STATUS_DRAFT,
                'file_path' => UploadedFile::fake()->createWithContent('palsu.pdf', 'Ini bukan PDF sama sekali.'),
            ])
            ->call('create')
            ->assertHasFormErrors(['file_path']);
    }

    public function test_pdf_larger_than_10mb_is_rejected(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $category = PpidCategory::where('slug', 'informasi-berkala')->firstOrFail();

        $oversized = "%PDF-1.4\n".str_repeat('x', 11 * 1024 * 1024);

        Livewire::test(CreatePpidDocument::class)
            ->fillForm([
                'title' => 'PDF Terlalu Besar',
                'category_id' => $category->id,
                'status' => PpidDocument::STATUS_DRAFT,
                'file_path' => UploadedFile::fake()->createWithContent('besar.pdf', $oversized),
            ])
            ->call('create')
            ->assertHasFormErrors(['file_path']);
    }

    public function test_document_status_transitions_are_saved(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $category = PpidCategory::where('slug', 'informasi-serta-merta')->firstOrFail();

        Livewire::test(CreatePpidDocument::class)
            ->fillForm([
                'title' => 'Dokumen Status',
                'category_id' => $category->id,
                'status' => PpidDocument::STATUS_DRAFT,
                'file_path' => UploadedFile::fake()->createWithContent('status.pdf', "%PDF-1.4\n%%EOF"),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $document = PpidDocument::where('title', 'Dokumen Status')->firstOrFail();
        $this->assertEquals(PpidDocument::STATUS_DRAFT, $document->status);

        Livewire::test(EditPpidDocument::class, ['record' => $document->getRouteKey()])
            ->fillForm(['status' => PpidDocument::STATUS_PUBLISHED])
            ->call('save')
            ->assertHasNoFormErrors();

        $document->refresh();
        $this->assertEquals(PpidDocument::STATUS_PUBLISHED, $document->status);

        Livewire::test(EditPpidDocument::class, ['record' => $document->getRouteKey()])
            ->fillForm(['status' => PpidDocument::STATUS_ARCHIVED])
            ->call('save')
            ->assertHasNoFormErrors();

        $document->refresh();
        $this->assertEquals(PpidDocument::STATUS_ARCHIVED, $document->status);
    }

    public function test_document_filename_is_sanitized_against_path_traversal(): void
    {
        $name = PpidDocumentResource::safeStoredFileName('../../evil.pdf');

        $this->assertStringNotContainsString('/', $name);
        $this->assertStringNotContainsString('..', $name);
        $this->assertStringEndsWith('.pdf', $name);

        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $category = PpidCategory::where('slug', 'informasi-berkala')->firstOrFail();

        Livewire::test(CreatePpidDocument::class)
            ->fillForm([
                'title' => 'Dokumen Aman',
                'category_id' => $category->id,
                'status' => PpidDocument::STATUS_DRAFT,
                'file_path' => UploadedFile::fake()->createWithContent('../../evil.pdf', "%PDF-1.4\n%%EOF"),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $document = PpidDocument::where('title', 'Dokumen Aman')->firstOrFail();

        $storedName = basename($document->file_path);

        $this->assertStringNotContainsString('/', $storedName);
        $this->assertStringNotContainsString('..', $storedName);
        $this->assertStringEndsWith('.pdf', $storedName);
        $this->assertTrue(Storage::disk('public')->exists($document->file_path));
    }

    public function test_deleting_document_removes_pdf_from_disk(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $path = 'lampiran/ppid/berkas.pdf';
        Storage::disk('public')->put($path, '%PDF-1.4');

        $category = PpidCategory::where('slug', 'informasi-berkala')->firstOrFail();

        $document = PpidDocument::create([
            'title' => 'Hapus Dokumen',
            'file_path' => $path,
            'file_size' => Storage::disk('public')->size($path),
            'category_id' => $category->id,
            'status' => PpidDocument::STATUS_DRAFT,
        ]);

        $document->delete();

        $this->assertFalse(Storage::disk('public')->exists($path));
    }

    public function test_ppid_role_can_manage_ppid_resources(): void
    {
        $ppid = User::factory()->create();
        $ppid->assignRole('admin_ppid_sop');
        $this->actingAs($ppid);

        $this->assertTrue(PpidDocumentResource::canViewAny());
        $this->assertTrue(PpidDocumentResource::canCreate());
        $this->assertTrue(PpidCategoryResource::canViewAny());
    }

    public function test_user_without_ppid_read_permission_cannot_access_resource(): void
    {
        $redaksi = User::factory()->create();
        $redaksi->assignRole('admin_redaksi_berita');
        $this->actingAs($redaksi);

        $this->assertFalse(PpidDocumentResource::canViewAny());
        $this->assertFalse(PpidDocumentResource::canCreate());
        $this->assertFalse(PpidCategoryResource::canViewAny());

        $this->get('/admin/ppid-documents')->assertForbidden();
    }

    private function getSeededAdmin(): User
    {
        return User::where('email', 'admin@disdikpora.karangasemkab.go.id')->firstOrFail();
    }
}
