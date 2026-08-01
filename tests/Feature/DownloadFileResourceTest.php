<?php

namespace Tests\Feature;

use App\Filament\Resources\DownloadFileResource;
use App\Filament\Resources\DownloadFileResource\Pages\CreateDownloadFile;
use App\Filament\Resources\DownloadFileResource\Pages\EditDownloadFile;
use App\Models\DownloadFile;
use App\Models\User;
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
                'description' => 'Formulir untuk pengujian.',
                'type' => DownloadFile::TYPE_FORMULIR,
                'status' => DownloadFile::STATUS_PUBLISHED,
                'file_path' => UploadedFile::fake()->createWithContent('formulir-uji.pdf', "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF"),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $file = DownloadFile::where('title', 'Formulir Uji Unduhan')->firstOrFail();

        $this->assertEquals(DownloadFile::TYPE_FORMULIR, $file->type);
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
                'type' => '',
                'status' => DownloadFile::STATUS_DRAFT,
            ])
            ->call('create')
            ->assertHasFormErrors(['title' => 'required', 'type' => 'required', 'file_path' => 'required']);
    }

    public function test_download_file_rejects_non_pdf(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateDownloadFile::class)
            ->fillForm([
                'title' => 'Berkas Salah Format',
                'type' => DownloadFile::TYPE_JUKNIS,
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
                'type' => DownloadFile::TYPE_LAINNYA,
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
                'type' => DownloadFile::TYPE_FORMULIR,
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
                'type' => DownloadFile::TYPE_FORMULIR,
                'status' => DownloadFile::STATUS_DRAFT,
                'file_path' => UploadedFile::fake()->createWithContent('status.pdf', "%PDF-1.4\n%%EOF"),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $file = DownloadFile::where('title', 'Berkas Status')->firstOrFail();
        $this->assertEquals(DownloadFile::STATUS_DRAFT, $file->status);

        Livewire::test(EditDownloadFile::class, ['record' => $file->getRouteKey()])
            ->fillForm(['status' => DownloadFile::STATUS_ARCHIVED, 'type' => DownloadFile::TYPE_JUKNIS])
            ->call('save')
            ->assertHasNoFormErrors();

        $file->refresh();
        $this->assertEquals(DownloadFile::STATUS_ARCHIVED, $file->status);
        $this->assertEquals(DownloadFile::TYPE_JUKNIS, $file->type);
    }

    public function test_replacing_file_on_update_removes_old_pdf(): void
    {
        $oldPath = 'lampiran/unduhan/lama.pdf';
        Storage::disk('public')->put($oldPath, '%PDF-1.4 (lama)');

        $file = DownloadFile::create([
            'title' => 'Ganti Berkas Unduhan',
            'type' => DownloadFile::TYPE_FORMULIR,
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
            'type' => DownloadFile::TYPE_JUKNIS,
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
