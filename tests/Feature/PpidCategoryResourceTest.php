<?php

namespace Tests\Feature;

use App\Filament\Resources\PpidCategoryResource;
use App\Filament\Resources\PpidCategoryResource\Pages\CreatePpidCategory;
use App\Filament\Resources\PpidCategoryResource\Pages\EditPpidCategory;
use App\Models\PpidCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PpidCategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    /** ============================== CRUD Kategori PPID ============================== */
    public function test_creating_ppid_category_via_form_persists_record_and_audit_log(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreatePpidCategory::class)
            ->fillForm([
                'name' => 'Informasi Uji Khusus',
                'slug' => 'informasi-uji-khusus',
                'description' => 'Kategori informasi untuk pengujian.',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $category = PpidCategory::where('slug', 'informasi-uji-khusus')->firstOrFail();
        $this->assertEquals('Informasi Uji Khusus', $category->name);

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => PpidCategory::class,
            'model_id' => $category->id,
            'action' => 'create',
            'user_id' => $admin->id,
        ]);
    }

    public function test_ppid_category_name_and_slug_are_required_and_unique(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $existing = PpidCategory::firstOrFail();

        Livewire::test(CreatePpidCategory::class)
            ->fillForm([
                'name' => '',
                'slug' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required', 'slug' => 'required']);

        Livewire::test(CreatePpidCategory::class)
            ->fillForm([
                'name' => 'Nama Baru Unik',
                'slug' => $existing->slug,
            ])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'unique']);
    }

    public function test_ppid_category_name_is_unique_even_with_different_slug(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $existing = PpidCategory::firstOrFail();

        Livewire::test(CreatePpidCategory::class)
            ->fillForm([
                'name' => $existing->name,
                'slug' => 'slug-lain-123',
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'unique']);
    }

    public function test_ppid_category_slug_must_be_url_safe(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreatePpidCategory::class)
            ->fillForm([
                'name' => 'Slug Tidak Aman',
                'slug' => 'Tidak Aman!',
            ])
            ->call('create')
            ->assertHasFormErrors(['slug']);
    }

    public function test_ppid_category_can_be_updated_and_records_audit_log(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $category = PpidCategory::firstOrFail();

        Livewire::test(EditPpidCategory::class, ['record' => $category->getRouteKey()])
            ->fillForm(['description' => 'Deskripsi kategori diperbarui.'])
            ->call('save')
            ->assertHasNoFormErrors();

        $category->refresh();
        $this->assertEquals('Deskripsi kategori diperbarui.', $category->description);

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => PpidCategory::class,
            'model_id' => $category->id,
            'action' => 'update',
            'user_id' => $admin->id,
        ]);
    }

    public function test_ppid_category_can_be_deleted_and_records_audit_log(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $category = PpidCategory::create([
            'name' => 'Kategori Sementara',
            'slug' => 'kategori-sementara-ppid',
        ]);

        $category->delete();

        $this->assertDatabaseMissing('ppid_categories', ['id' => $category->id]);

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => PpidCategory::class,
            'model_id' => $category->id,
            'action' => 'delete',
            'user_id' => $admin->id,
        ]);
    }

    /** ============================== RBAC Kategori PPID ============================== */
    public function test_ppid_role_can_manage_ppid_categories(): void
    {
        $ppid = User::factory()->create();
        $ppid->assignRole('admin_ppid_sop');
        $this->actingAs($ppid);

        $this->assertTrue(PpidCategoryResource::canViewAny());
        $this->assertTrue(PpidCategoryResource::canCreate());
        $this->assertTrue(PpidCategoryResource::canEdit(PpidCategory::first()));
        $this->assertTrue(PpidCategoryResource::canDelete(PpidCategory::first()));
    }

    public function test_redaksi_role_cannot_manage_ppid_categories(): void
    {
        $redaksi = User::factory()->create();
        $redaksi->assignRole('admin_redaksi_berita');
        $this->actingAs($redaksi);

        $this->assertFalse(PpidCategoryResource::canViewAny());
        $this->assertFalse(PpidCategoryResource::canCreate());
        $this->assertFalse(PpidCategoryResource::canDelete(PpidCategory::first()));

        $this->get('/admin/ppid-categories')->assertForbidden();
    }

    public function test_ppid_category_resource_form_and_table_are_registered(): void
    {
        $this->assertSame(PpidCategory::class, PpidCategoryResource::getModel());
    }

    private function getSeededAdmin(): User
    {
        return User::where('email', 'admin@disdikpora.karangasemkab.go.id')->firstOrFail();
    }
}
