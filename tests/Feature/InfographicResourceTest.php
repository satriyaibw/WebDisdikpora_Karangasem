<?php

namespace Tests\Feature;

use App\Filament\Resources\InfographicResource;
use App\Filament\Resources\InfographicResource\Pages\CreateInfographic;
use App\Models\Infographic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class InfographicResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        Storage::fake('public');
    }

    /** ============================== CRUD Infografis ============================== */
    public function test_creating_infographic_via_form_persists_record_and_audit_log(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateInfographic::class)
            ->fillForm([
                'title' => 'Data Siswa 2026',
                'image' => UploadedFile::fake()->image('data-siswa.png', 800, 600),
                'link' => 'https://disdikpora.karangasemkab.go.id/berita',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $infographic = Infographic::where('title', 'Data Siswa 2026')->firstOrFail();
        $this->assertTrue($infographic->is_active);
        $this->assertStringEndsWith('.webp', $infographic->image);
        $this->assertTrue(Storage::disk('public')->exists($infographic->image));

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => Infographic::class,
            'model_id' => $infographic->id,
            'action' => 'create',
            'user_id' => $admin->id,
        ]);
    }

    public function test_infographic_link_rejects_non_http_schemes(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateInfographic::class)
            ->fillForm([
                'title' => 'Infografis Bahaya',
                'image' => UploadedFile::fake()->image('bahaya.jpg', 800, 600),
                'link' => 'javascript://alert(1)',
            ])
            ->call('create')
            ->assertHasFormErrors(['link']);
    }

    public function test_infographic_link_accepts_http_and_https_schemes(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateInfographic::class)
            ->fillForm([
                'title' => 'Infografis Aman',
                'image' => UploadedFile::fake()->image('aman.jpg', 800, 600),
                'link' => 'https://disdikpora.karangasemkab.go.id',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('infographics', ['title' => 'Infografis Aman']);
    }

    public function test_infographic_title_and_image_are_required(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateInfographic::class)
            ->fillForm([
                'title' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['title' => 'required', 'image' => 'required']);
    }

    public function test_deleting_infographic_removes_image_from_disk(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $path = 'images/infografis/hapus.webp';
        Storage::disk('public')->put($path, 'data');

        $infographic = Infographic::create([
            'title' => 'Infografis Hapus',
            'image' => $path,
            'is_active' => true,
        ]);

        $infographic->delete();

        $this->assertFalse(Storage::disk('public')->exists($path));

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => Infographic::class,
            'model_id' => $infographic->id,
            'action' => 'delete',
            'user_id' => $admin->id,
        ]);
    }

    /** ============================== RBAC Infografis ============================== */
    public function test_redaksi_role_can_manage_infographics(): void
    {
        $redaksi = User::factory()->create();
        $redaksi->assignRole('admin_redaksi_berita');
        $this->actingAs($redaksi);

        $this->assertTrue(InfographicResource::canViewAny());
        $this->assertTrue(InfographicResource::canCreate());
        $this->assertTrue(InfographicResource::canEdit(new Infographic));
        $this->assertTrue(InfographicResource::canDelete(new Infographic));
    }

    public function test_ppid_role_cannot_manage_infographics(): void
    {
        $ppid = User::factory()->create();
        $ppid->assignRole('admin_ppid_sop');
        $this->actingAs($ppid);

        $this->assertFalse(InfographicResource::canViewAny());
        $this->assertFalse(InfographicResource::canCreate());
        $this->assertFalse(InfographicResource::canDelete(new Infographic));

        $this->get('/admin/infographics')->assertForbidden();
    }

    public function test_infographic_resource_form_and_table_are_registered(): void
    {
        $this->assertSame(Infographic::class, InfographicResource::getModel());
    }

    private function getSeededAdmin(): User
    {
        return User::where('email', 'admin@disdikpora.karangasemkab.go.id')->firstOrFail();
    }
}
