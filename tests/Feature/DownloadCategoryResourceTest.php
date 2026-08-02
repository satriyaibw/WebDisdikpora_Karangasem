<?php

namespace Tests\Feature;

use App\Filament\Resources\DownloadCategoryResource;
use App\Filament\Resources\DownloadCategoryResource\Pages\CreateDownloadCategory;
use App\Filament\Resources\DownloadCategoryResource\Pages\EditDownloadCategory;
use App\Models\DownloadCategory;
use App\Models\DownloadFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DownloadCategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_creating_download_category_via_form_persists_record_and_audit_log(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateDownloadCategory::class)
            ->fillForm([
                'name' => 'Peraturan & Pedoman',
                'slug' => 'peraturan-pedoman',
                'sort_order' => 5,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $category = DownloadCategory::where('slug', 'peraturan-pedoman')->firstOrFail();
        $this->assertEquals('Peraturan & Pedoman', $category->name);
        $this->assertEquals(5, $category->sort_order);

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => DownloadCategory::class,
            'model_id' => $category->id,
            'action' => 'create',
            'user_id' => $admin->id,
        ]);
    }

    public function test_download_category_name_and_slug_are_required_and_unique(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $existing = DownloadCategory::firstOrFail();

        Livewire::test(CreateDownloadCategory::class)
            ->fillForm(['name' => '', 'slug' => ''])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required', 'slug' => 'required']);

        Livewire::test(CreateDownloadCategory::class)
            ->fillForm([
                'name' => 'Nama Duplikat',
                'slug' => $existing->slug,
            ])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'unique']);
    }

    public function test_download_category_slug_must_be_url_safe(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateDownloadCategory::class)
            ->fillForm([
                'name' => 'Slug Salah Format',
                'slug' => 'Slug Salah / Format',
            ])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'regex']);
    }

    public function test_deleting_category_keeps_its_files(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $category = DownloadCategory::where('slug', 'formulir')->firstOrFail();
        $file = DownloadFile::where('category_id', $category->id)->firstOrFail();

        Livewire::test(EditDownloadCategory::class, ['record' => $category->getRouteKey()])
            ->callAction('delete')
            ->assertSuccessful();

        $this->assertDatabaseMissing('download_categories', ['id' => $category->id]);
        $this->assertDatabaseHas('download_files', ['id' => $file->id, 'category_id' => null]);
    }

    public function test_layanan_role_can_manage_download_categories(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin_layanan_publik');
        $this->actingAs($user);

        $this->assertTrue(DownloadCategoryResource::canViewAny());
        $this->assertTrue(DownloadCategoryResource::canCreate());
        $this->assertTrue(DownloadCategoryResource::canEdit(DownloadCategory::first()));
    }

    public function test_user_without_unduhan_permission_cannot_manage_categories(): void
    {
        $redaksi = User::factory()->create();
        $redaksi->assignRole('admin_redaksi_berita');
        $this->actingAs($redaksi);

        $this->assertFalse(DownloadCategoryResource::canViewAny());
        $this->assertFalse(DownloadCategoryResource::canCreate());

        $this->get('/admin/download-categories')->assertForbidden();
    }

    private function getSeededAdmin(): User
    {
        return User::where('email', 'admin@disdikpora.karangasemkab.go.id')->firstOrFail();
    }
}
