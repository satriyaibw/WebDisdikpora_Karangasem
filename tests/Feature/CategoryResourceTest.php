<?php

namespace Tests\Feature;

use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\CategoryResource\Pages\CreateCategory;
use App\Filament\Resources\CategoryResource\Pages\EditCategory;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    /** ============================== CRUD Kategori ============================== */
    public function test_creating_category_via_form_persists_record_and_audit_log(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateCategory::class)
            ->fillForm([
                'name' => 'Inovasi',
                'slug' => 'inovasi',
                'description' => 'Kategori inovasi untuk pengujian.',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $category = Category::where('slug', 'inovasi')->firstOrFail();
        $this->assertEquals('Inovasi', $category->name);

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => Category::class,
            'model_id' => $category->id,
            'action' => 'create',
            'user_id' => $admin->id,
        ]);
    }

    public function test_category_name_and_slug_are_required_and_unique(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $existing = Category::firstOrFail();

        Livewire::test(CreateCategory::class)
            ->fillForm([
                'name' => '',
                'slug' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required', 'slug' => 'required']);

        Livewire::test(CreateCategory::class)
            ->fillForm([
                'name' => 'Duplikat Slug',
                'slug' => $existing->slug,
            ])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'unique']);
    }

    public function test_category_can_be_updated_and_records_audit_log(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $category = Category::firstOrFail();

        Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
            ->fillForm(['description' => 'Deskripsi kategori diperbarui.'])
            ->call('save')
            ->assertHasNoFormErrors();

        $category->refresh();
        $this->assertEquals('Deskripsi kategori diperbarui.', $category->description);

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => Category::class,
            'model_id' => $category->id,
            'action' => 'update',
            'user_id' => $admin->id,
        ]);
    }

    public function test_category_can_be_deleted_and_records_audit_log(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $category = Category::create([
            'name' => 'Kategori Sementara',
            'slug' => 'kategori-sementara',
        ]);

        $category->delete();

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => Category::class,
            'model_id' => $category->id,
            'action' => 'delete',
            'user_id' => $admin->id,
        ]);
    }

    /** ============================== RBAC Kategori ============================== */
    public function test_redaksi_role_can_manage_categories(): void
    {
        $redaksi = User::factory()->create();
        $redaksi->assignRole('admin_redaksi_berita');
        $this->actingAs($redaksi);

        $this->assertTrue(CategoryResource::canViewAny());
        $this->assertTrue(CategoryResource::canCreate());
        $this->assertTrue(CategoryResource::canEdit(Category::first()));
        $this->assertTrue(CategoryResource::canDelete(Category::first()));
    }

    public function test_ppid_role_cannot_manage_categories(): void
    {
        $ppid = User::factory()->create();
        $ppid->assignRole('admin_ppid_sop');
        $this->actingAs($ppid);

        $this->assertFalse(CategoryResource::canViewAny());
        $this->assertFalse(CategoryResource::canCreate());
        $this->assertFalse(CategoryResource::canDelete(Category::first()));

        $this->get('/admin/categories')->assertForbidden();
    }

    public function test_category_resource_form_and_table_are_registered(): void
    {
        $this->assertSame(Category::class, CategoryResource::getModel());
    }

    private function getSeededAdmin(): User
    {
        return User::where('email', 'admin@disdikpora.karangasemkab.go.id')->firstOrFail();
    }
}
