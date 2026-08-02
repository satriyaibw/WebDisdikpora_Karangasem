<?php

namespace Tests\Feature;

use App\Filament\Resources\DownloadFileResource;
use App\Filament\Resources\DownloadFileResource\Pages\CreateDownloadFile;
use App\Filament\Resources\DownloadFileResource\Pages\EditDownloadFile;
use App\Filament\Resources\DownloadFileResource\Pages\ListDownloadFiles;
use App\Models\DownloadCategory;
use App\Models\DownloadFile;
use App\Models\User;
use Database\Seeders\DownloadFileSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DownloadFileResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        Storage::fake('public');
    }

    public function test_creating_download_file_via_form_persists_record_and_audit_log(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateDownloadFile::class)
            ->fillForm([
                'title' => 'Formulir Uji Unduhan',
                'slug' => 'formulir-uji-unduhan',
                'description' => 'Formulir untuk pengujian.',
                'category_id' => DownloadCategory::where('slug', 'formulir')->value('id'),
                'status' => DownloadFile::STATUS_PUBLISHED,
                'file_path' => UploadedFile::fake()->createWithContent('formulir-uji.pdf', "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF"),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $file = DownloadFile::where('title', 'Formulir Uji Unduhan')->firstOrFail();

        $this->assertEquals('formulir', $file->category?->slug);
        $this->assertEquals(DownloadFile::STATUS_PUBLISHED, $file->status);
        $this->assertTrue(Storage::disk('public')->exists($file->file_path));
        $this->assertStringEndsWith('.pdf', $file->file_path);

        $storedSize = Storage::disk('public')->size($file->file_path);
        $this->assertEquals($storedSize, $file->file_size);

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => DownloadFile::class,
            'model_id' => $file->id,
            'action' => 'create',
            'user_id' => $admin->id,
        ]);
    }

    public function test_download_file_title_type_and_file_are_required(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateDownloadFile::class)
            ->fillForm([
                'title' => '',
                'category_id' => null,
                'status' => DownloadFile::STATUS_DRAFT,
            ])
            ->call('create')
            ->assertHasFormErrors(['title' => 'required', 'category_id' => 'required', 'file_path' => 'required']);
    }

    public function test_download_file_rejects_non_pdf(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateDownloadFile::class)
            ->fillForm([
                'title' => 'Berkas Salah Format',
                'category_id' => DownloadCategory::where('slug', 'juknis')->value('id'),
                'status' => DownloadFile::STATUS_DRAFT,
                'file_path' => UploadedFile::fake()->create('juknis.txt', 100),
            ])
            ->call('create')
            ->assertHasFormErrors(['file_path']);
    }

    public function test_download_file_rejects_pdf_with_wrong_magic_bytes(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateDownloadFile::class)
            ->fillForm([
                'title' => 'Berkas PDF Palsu',
                'category_id' => DownloadCategory::where('slug', 'lainnya')->value('id'),
                'status' => DownloadFile::STATUS_DRAFT,
                'file_path' => UploadedFile::fake()->createWithContent('palsu.pdf', 'Ini bukan PDF sama sekali.'),
            ])
            ->call('create')
            ->assertHasFormErrors(['file_path']);
    }

    public function test_download_file_rejects_pdf_larger_than_10mb(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $oversized = "%PDF-1.4\n".str_repeat('x', 11 * 1024 * 1024);

        Livewire::test(CreateDownloadFile::class)
            ->fillForm([
                'title' => 'Berkas PDF Besar',
                'category_id' => DownloadCategory::where('slug', 'formulir')->value('id'),
                'status' => DownloadFile::STATUS_DRAFT,
                'file_path' => UploadedFile::fake()->createWithContent('besar.pdf', $oversized),
            ])
            ->call('create')
            ->assertHasFormErrors(['file_path']);
    }

    public function test_download_file_status_and_type_transitions_are_saved(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateDownloadFile::class)
            ->fillForm([
                'title' => 'Berkas Status',
                'slug' => 'berkas-status',
                'category_id' => DownloadCategory::where('slug', 'formulir')->value('id'),
                'status' => DownloadFile::STATUS_DRAFT,
                'file_path' => UploadedFile::fake()->createWithContent('status.pdf', "%PDF-1.4\n%%EOF"),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $file = DownloadFile::where('title', 'Berkas Status')->firstOrFail();
        $this->assertEquals(DownloadFile::STATUS_DRAFT, $file->status);

        Livewire::test(EditDownloadFile::class, ['record' => $file->getRouteKey()])
            ->fillForm(['status' => DownloadFile::STATUS_ARCHIVED, 'category_id' => DownloadCategory::where('slug', 'juknis')->value('id')])
            ->call('save')
            ->assertHasNoFormErrors();

        $file->refresh();
        $this->assertEquals(DownloadFile::STATUS_ARCHIVED, $file->status);
        $this->assertEquals('juknis', $file->category?->slug);
    }

    public function test_replacing_file_on_update_removes_old_pdf(): void
    {
        $oldPath = 'lampiran/unduhan/lama.pdf';
        Storage::disk('public')->put($oldPath, '%PDF-1.4 (lama)');

        $file = DownloadFile::create([
            'title' => 'Ganti Berkas Unduhan',
            'slug' => 'ganti-berkas-unduhan',
            'category_id' => DownloadCategory::where('slug', 'formulir')->value('id'),
            'file_path' => $oldPath,
            'file_size' => Storage::disk('public')->size($oldPath),
            'status' => DownloadFile::STATUS_DRAFT,
        ]);

        $newPath = 'lampiran/unduhan/baru.pdf';
        Storage::disk('public')->put($newPath, '%PDF-1.4 (baru)');

        $file->update([
            'file_path' => $newPath,
            'file_size' => Storage::disk('public')->size($newPath),
        ]);

        $this->assertTrue(Storage::disk('public')->exists($newPath));
        $this->assertFalse(Storage::disk('public')->exists($oldPath), 'Berkas lama harus terhapus saat diganti.');
    }

    public function test_deleting_download_file_removes_pdf_from_disk(): void
    {
        $path = 'lampiran/unduhan/berkas.pdf';
        Storage::disk('public')->put($path, '%PDF-1.4');

        $file = DownloadFile::create([
            'title' => 'Hapus Berkas Unduhan',
            'slug' => 'hapus-berkas-unduhan',
            'category_id' => DownloadCategory::where('slug', 'juknis')->value('id'),
            'file_path' => $path,
            'file_size' => Storage::disk('public')->size($path),
            'status' => DownloadFile::STATUS_DRAFT,
        ]);

        $file->delete();

        $this->assertFalse(Storage::disk('public')->exists($path));
    }

    public function test_layanan_role_can_manage_download_files(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin_layanan_publik');
        $this->actingAs($user);

        $this->assertTrue(DownloadFileResource::canViewAny());
        $this->assertTrue(DownloadFileResource::canCreate());
        $this->assertTrue(DownloadFileResource::canEdit(DownloadFile::first()));
    }

    public function test_download_file_slug_is_required_and_unique(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $existing = DownloadFile::firstOrFail();

        Livewire::test(CreateDownloadFile::class)
            ->fillForm([
                'title' => 'Berkas Tanpa Slug',
                'slug' => '',
                'category_id' => DownloadCategory::where('slug', 'formulir')->value('id'),
                'status' => DownloadFile::STATUS_DRAFT,
                'file_path' => UploadedFile::fake()->createWithContent('slug-kosong.pdf', "%PDF-1.4\n%%EOF"),
            ])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'required']);

        Livewire::test(CreateDownloadFile::class)
            ->fillForm([
                'title' => 'Berkas Slug Duplikat',
                'slug' => $existing->slug,
                'category_id' => DownloadCategory::where('slug', 'formulir')->value('id'),
                'status' => DownloadFile::STATUS_DRAFT,
                'file_path' => UploadedFile::fake()->createWithContent('slug-duplikat.pdf', "%PDF-1.4\n%%EOF"),
            ])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'unique']);
    }

    public function test_download_file_slug_must_be_alpha_dash(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateDownloadFile::class)
            ->fillForm([
                'title' => 'Berkas Slug Salah Format',
                'slug' => 'Berkas Slug Salah / X',
                'category_id' => DownloadCategory::where('slug', 'formulir')->value('id'),
                'status' => DownloadFile::STATUS_DRAFT,
                'file_path' => UploadedFile::fake()->createWithContent('slug-salah.pdf', "%PDF-1.4\n%%EOF"),
            ])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'alpha_dash']);
    }

    public function test_download_action_is_disabled_when_file_is_missing(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $file = DownloadFile::create([
            'title' => 'Berkas Tanpa PDF',
            'slug' => 'berkas-tanpa-pdf',
            'category_id' => DownloadCategory::where('slug', 'formulir')->value('id'),
            'status' => DownloadFile::STATUS_DRAFT,
            'file_path' => 'lampiran/unduhan/hilang.pdf',
        ]);

        Livewire::test(ListDownloadFiles::class)
            ->assertTableActionDisabled('download', $file);
    }

    public function test_download_file_seeder_does_not_duplicate_when_title_is_renamed(): void
    {
        $this->seed(DownloadFileSeeder::class);

        $file = DownloadFile::where('slug', 'formulir-pendaftaran-peserta-didik-baru')->firstOrFail();
        $oldPath = $file->file_path;

        $file->update(['title' => 'Formulir PPDB Terbaru']);

        $this->seed(DownloadFileSeeder::class);

        $this->assertSame(1, DownloadFile::where('slug', 'formulir-pendaftaran-peserta-didik-baru')->count());
        $file->refresh();

        $this->assertSame('Formulir Pendaftaran Peserta Didik Baru', $file->title);
        $this->assertSame($oldPath, $file->file_path);
        $this->assertTrue(Storage::disk('public')->exists($oldPath), 'Berkas seeder harus dipakai ulang, bukan dibuat duplikat.');
    }

    public function test_user_without_unduhan_permission_cannot_access_resource(): void
    {
        $redaksi = User::factory()->create();
        $redaksi->assignRole('admin_redaksi_berita');
        $this->actingAs($redaksi);

        $this->assertFalse(DownloadFileResource::canViewAny());
        $this->assertFalse(DownloadFileResource::canCreate());

        $this->get('/admin/download-files')->assertForbidden();
    }

    private function getSeededAdmin(): User
    {
        return User::where('email', 'admin@disdikpora.karangasemkab.go.id')->firstOrFail();
    }
}
