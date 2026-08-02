<?php

namespace Tests\Feature;

use App\Filament\Resources\RelatedLinkResource;
use App\Filament\Resources\RelatedLinkResource\Pages\CreateRelatedLink;
use App\Models\RelatedLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RelatedLinkResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_creating_link_via_form_persists_record_and_audit_log(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateRelatedLink::class)
            ->fillForm([
                'name' => 'JDIH Karangasem',
                'url' => 'https://jdih.karangasemkab.go.id',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $link = RelatedLink::where('name', 'JDIH Karangasem')->firstOrFail();
        $this->assertEquals('https://jdih.karangasemkab.go.id', $link->url);
        $this->assertTrue($link->is_active);

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => RelatedLink::class,
            'model_id' => $link->id,
            'action' => 'create',
            'user_id' => $admin->id,
        ]);
    }

    public function test_link_name_and_url_are_required_and_url_must_be_valid(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateRelatedLink::class)
            ->fillForm(['name' => '', 'url' => ''])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required', 'url' => 'required']);

        Livewire::test(CreateRelatedLink::class)
            ->fillForm([
                'name' => 'URL Tidak Valid',
                'url' => 'bukan-url',
            ])
            ->call('create')
            ->assertHasFormErrors(['url' => 'url']);
    }

    public function test_link_can_be_inactivated_and_deleted(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $link = RelatedLink::create([
            'name' => 'Tautan Sementara',
            'url' => 'https://example.test',
            'sort_order' => 9,
            'is_active' => true,
        ]);

        $link->update(['is_active' => false]);
        $this->assertFalse($link->fresh()->is_active);

        $this->assertTrue(RelatedLinkResource::canDelete($link));

        $link->delete();
        $this->assertDatabaseMissing('related_links', ['id' => $link->id]);
    }

    public function test_non_privileged_role_cannot_manage_links(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin_layanan_publik');
        $this->actingAs($user);

        $this->assertFalse(RelatedLinkResource::canViewAny());
        $this->assertFalse(RelatedLinkResource::canCreate());

        $this->get('/admin/related-links')->assertForbidden();
    }

    private function getSeededAdmin(): User
    {
        return User::where('email', 'admin@disdikpora.karangasemkab.go.id')->firstOrFail();
    }
}
