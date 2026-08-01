<?php

namespace Tests\Feature;

use App\Filament\Resources\BidangResource;
use App\Filament\Resources\BidangResource\Pages\CreateBidang;
use App\Filament\Resources\BidangResource\Pages\EditBidang;
use App\Models\Bidang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BidangResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_creating_bidang_via_form_persists_record_and_audit_log(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateBidang::class)
            ->fillForm([
                'name' => 'Pengembangan Teknologi Informasi',
                'slug' => 'pengembangan-teknologi-informasi',
                'description' => 'Bidang pengembangan TIK untuk uji.',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $bidang = Bidang::where('slug', 'pengembangan-teknologi-informasi')->firstOrFail();
        $this->assertEquals('Pengembangan Teknologi Informasi', $bidang->name);

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => Bidang::class,
            'model_id' => $bidang->id,
            'action' => 'create',
            'user_id' => $admin->id,
        ]);
    }

    public function test_bidang_slug_is_required_and_unique(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $existing = Bidang::firstOrFail();

        Livewire::test(CreateBidang::class)
            ->fillForm([
                'name' => 'Duplikat Slug',
                'slug' => $existing->slug,
            ])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'unique']);

        Livewire::test(CreateBidang::class)
            ->fillForm([
                'name' => 'Tanpa Slug',
                'slug' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'required']);
    }

    public function test_bidang_can_be_updated_and_deleted(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $bidang = Bidang::firstOrFail();

        Livewire::test(EditBidang::class, ['record' => $bidang->getRouteKey()])
            ->fillForm(['description' => 'Deskripsi diperbarui.'])
            ->call('save')
            ->assertHasNoFormErrors();

        $bidang->refresh();
        $this->assertEquals('Deskripsi diperbarui.', $bidang->description);

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => Bidang::class,
            'model_id' => $bidang->id,
            'action' => 'update',
            'user_id' => $admin->id,
        ]);
    }

    public function test_sop_role_can_manage_bidangs(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin_ppid_sop');
        $this->actingAs($user);

        $this->assertTrue(BidangResource::canViewAny());
        $this->assertTrue(BidangResource::canCreate());
        $this->assertTrue(BidangResource::canEdit(Bidang::first()));
    }

    public function test_layanan_role_cannot_manage_bidangs(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin_layanan_publik');
        $this->actingAs($user);

        $this->assertFalse(BidangResource::canViewAny());
        $this->assertFalse(BidangResource::canCreate());
        $this->assertFalse(BidangResource::canDelete(Bidang::first()));

        $this->get('/admin/bidangs')->assertForbidden();
    }

    private function getSeededAdmin(): User
    {
        return User::where('email', 'admin@disdikpora.karangasemkab.go.id')->firstOrFail();
    }
}
