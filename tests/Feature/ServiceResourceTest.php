<?php

namespace Tests\Feature;

use App\Filament\Resources\ServiceResource;
use App\Filament\Resources\ServiceResource\Pages\CreateService;
use App\Filament\Resources\ServiceResource\Pages\EditService;
use App\Filament\Resources\ServiceResource\Pages\ListServices;
use App\Models\Bidang;
use App\Models\Service;
use App\Models\User;
use App\Rules\ValidPdfFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Livewire\Livewire;
use Tests\TestCase;

class ServiceResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        Storage::fake('public');
    }

    public function test_creating_service_via_form_persists_record_and_audit_log(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $bidang = Bidang::where('slug', 'sekretariat')->firstOrFail();

        Livewire::test(CreateService::class)
            ->fillForm([
                'name' => 'Surat Keterangan Aktif Sekolah',
                'slug' => 'surat-keterangan-aktif-sekolah',
                'bidang_id' => $bidang->id,
                'short_description' => 'Keterangan aktif belajar untuk keperluan resmi.',
                'description' => '<p>Penjelasan layanan.</p>',
                'requirements' => '<ul><li>Fotokopi kartu pelajar</li></ul>',
                'procedure' => '<ol><li>Mengajukan permohonan</li></ol>',
                'estimated_time' => '2 Hari Kerja',
                'cost' => 'Rp 0 / Gratis',
                'pic_name' => 'Kasi Kurikulum',
                'pic_contact' => '(0363) 21034',
                'status' => Service::STATUS_PUBLISHED,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $service = Service::where('slug', 'surat-keterangan-aktif-sekolah')->firstOrFail();

        $this->assertEquals($bidang->id, $service->bidang_id);
        $this->assertEquals(Service::STATUS_PUBLISHED, $service->status);
        $this->assertNull($service->form_template);

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => Service::class,
            'model_id' => $service->id,
            'action' => 'create',
            'user_id' => $admin->id,
        ]);
    }

    public function test_service_name_and_slug_are_required(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateService::class)
            ->fillForm([
                'name' => '',
                'slug' => '',
                'status' => Service::STATUS_DRAFT,
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required', 'slug' => 'required']);
    }

    public function test_service_accepts_valid_pdf_template_and_persists_file_size(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateService::class)
            ->fillForm([
                'name' => 'Layanan Dengan Formulir',
                'slug' => 'layanan-dengan-formulir',
                'status' => Service::STATUS_DRAFT,
                'form_template' => UploadedFile::fake()->createWithContent('formulir.pdf', "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF"),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $service = Service::where('slug', 'layanan-dengan-formulir')->firstOrFail();

        $this->assertNotNull($service->form_template);
        $this->assertStringEndsWith('.pdf', $service->form_template);
        $this->assertTrue(Storage::disk('public')->exists($service->form_template));

        $storedSize = Storage::disk('public')->size($service->form_template);
        $this->assertEquals($storedSize, $service->file_size);
    }

    public function test_service_rejects_non_pdf_template(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateService::class)
            ->fillForm([
                'name' => 'Layanan Salah Format',
                'slug' => 'layanan-salah-format',
                'status' => Service::STATUS_DRAFT,
                'form_template' => UploadedFile::fake()->create('formulir.txt', 100),
            ])
            ->call('create')
            ->assertHasFormErrors(['form_template']);
    }

    public function test_service_rejects_pdf_with_wrong_magic_bytes(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Storage::disk('public')->put('lampiran/layanan/palsu.pdf', 'Ini bukan PDF sama sekali.');

        $validator = Validator::make(
            ['file' => 'lampiran/layanan/palsu.pdf'],
            ['file' => [new ValidPdfFile]]
        );

        $this->assertTrue($validator->fails());

        Livewire::test(CreateService::class)
            ->fillForm([
                'name' => 'Layanan PDF Palsu',
                'slug' => 'layanan-pdf-palsu',
                'status' => Service::STATUS_DRAFT,
                'form_template' => UploadedFile::fake()->createWithContent('palsu.pdf', 'Ini bukan PDF sama sekali.'),
            ])
            ->call('create')
            ->assertHasFormErrors(['form_template']);
    }

    public function test_service_rejects_pdf_larger_than_10mb(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $oversized = "%PDF-1.4\n".str_repeat('x', 11 * 1024 * 1024);

        Livewire::test(CreateService::class)
            ->fillForm([
                'name' => 'Layanan PDF Besar',
                'slug' => 'layanan-pdf-besar',
                'status' => Service::STATUS_DRAFT,
                'form_template' => UploadedFile::fake()->createWithContent('besar.pdf', $oversized),
            ])
            ->call('create')
            ->assertHasFormErrors(['form_template']);
    }

    public function test_replacing_template_on_update_removes_old_pdf(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $oldPath = 'lampiran/layanan/lama.pdf';
        Storage::disk('public')->put($oldPath, '%PDF-1.4 (lama)');

        $service = Service::create([
            'name' => 'Ganti Formulir',
            'slug' => 'ganti-formulir',
            'form_template' => $oldPath,
            'file_size' => Storage::disk('public')->size($oldPath),
            'status' => Service::STATUS_DRAFT,
        ]);

        $newPath = 'lampiran/layanan/baru.pdf';
        Storage::disk('public')->put($newPath, '%PDF-1.4 (baru)');

        $service->update([
            'form_template' => $newPath,
            'file_size' => Storage::disk('public')->size($newPath),
        ]);

        $this->assertTrue(Storage::disk('public')->exists($newPath));
        $this->assertFalse(Storage::disk('public')->exists($oldPath), 'Berkas lama harus terhapus saat diganti.');
        $this->assertDatabaseHas('audit_logs', [
            'model_type' => Service::class,
            'model_id' => $service->id,
            'action' => 'update',
            'user_id' => $admin->id,
        ]);
    }

    public function test_deleting_service_removes_template_from_disk(): void
    {
        $path = 'lampiran/layanan/berkas.pdf';
        Storage::disk('public')->put($path, '%PDF-1.4');

        $service = Service::create([
            'name' => 'Hapus Layanan',
            'slug' => 'hapus-layanan',
            'form_template' => $path,
            'file_size' => Storage::disk('public')->size($path),
            'status' => Service::STATUS_DRAFT,
        ]);

        $service->delete();

        $this->assertFalse(Storage::disk('public')->exists($path));
    }

    public function test_service_status_transitions_are_saved(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateService::class)
            ->fillForm([
                'name' => 'Layanan Status',
                'slug' => 'layanan-status',
                'status' => Service::STATUS_DRAFT,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $service = Service::where('slug', 'layanan-status')->firstOrFail();
        $this->assertEquals(Service::STATUS_DRAFT, $service->status);

        Livewire::test(EditService::class, ['record' => $service->getRouteKey()])
            ->fillForm(['status' => Service::STATUS_PUBLISHED])
            ->call('save')
            ->assertHasNoFormErrors();

        $service->refresh();
        $this->assertEquals(Service::STATUS_PUBLISHED, $service->status);
    }

    public function test_layanan_role_can_manage_services(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin_layanan_publik');
        $this->actingAs($user);

        $this->assertTrue(ServiceResource::canViewAny());
        $this->assertTrue(ServiceResource::canCreate());
        $this->assertTrue(ServiceResource::canEdit(Service::first()));
    }

    public function test_removing_template_on_update_clears_file_size_and_deletes_old_pdf(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $path = 'lampiran/layanan/akan-dihapus.pdf';
        Storage::disk('public')->put($path, '%PDF-1.4 (lama)');

        $service = Service::create([
            'name' => 'Hapus Template',
            'slug' => 'hapus-template',
            'form_template' => $path,
            'file_size' => Storage::disk('public')->size($path),
            'status' => Service::STATUS_DRAFT,
        ]);

        Livewire::test(EditService::class, ['record' => $service->getRouteKey()])
            ->fillForm([
                'name' => 'Hapus Template',
                'slug' => 'hapus-template',
                'status' => Service::STATUS_DRAFT,
                'form_template' => null,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $service->refresh();

        $this->assertNull($service->form_template);
        $this->assertNull($service->file_size, 'file_size harus kosong saat template dihapus.');
        $this->assertFalse(Storage::disk('public')->exists($path), 'Berkas lama harus terhapus saat template dihapus.');
    }

    public function test_download_action_follows_template_availability(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $path = 'lampiran/layanan/tersedia.pdf';
        Storage::disk('public')->put($path, '%PDF-1.4');

        $withTemplate = Service::create([
            'name' => 'Dengan Template',
            'slug' => 'dengan-template',
            'form_template' => $path,
            'file_size' => Storage::disk('public')->size($path),
            'status' => Service::STATUS_DRAFT,
        ]);

        $withoutTemplate = Service::create([
            'name' => 'Tanpa Template',
            'slug' => 'tanpa-template',
            'status' => Service::STATUS_DRAFT,
        ]);

        Livewire::test(ListServices::class)
            ->assertTableActionEnabled('download', $withTemplate)
            ->assertTableActionDisabled('download', $withoutTemplate);
    }

    public function test_user_without_layanan_permission_cannot_access_resource(): void
    {
        $redaksi = User::factory()->create();
        $redaksi->assignRole('admin_redaksi_berita');
        $this->actingAs($redaksi);

        $this->assertFalse(ServiceResource::canViewAny());
        $this->assertFalse(ServiceResource::canCreate());

        $this->get('/admin/services')->assertForbidden();
    }

    private function getSeededAdmin(): User
    {
        return User::where('email', 'admin@disdikpora.karangasemkab.go.id')->firstOrFail();
    }
}
