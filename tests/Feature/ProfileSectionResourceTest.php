<?php

namespace Tests\Feature;

use App\Filament\Resources\ProfileSectionResource;
use App\Filament\Resources\ProfileSectionResource\Pages\CreateProfileSection;
use App\Models\ProfileSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileSectionResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_creating_section_via_form_persists_record_and_audit_log(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateProfileSection::class)
            ->fillForm([
                'title' => 'Program Prioritas',
                'slug' => 'program-prioritas',
                'content' => '<p>Prioritas pembangunan pendidikan 2026.</p>',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $section = ProfileSection::where('slug', 'program-prioritas')->firstOrFail();
        $this->assertEquals('Program Prioritas', $section->title);
        $this->assertTrue($section->is_active);

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => ProfileSection::class,
            'model_id' => $section->id,
            'action' => 'create',
            'user_id' => $admin->id,
        ]);
    }

    public function test_section_title_and_slug_are_required_and_unique(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $existing = ProfileSection::firstOrFail();

        Livewire::test(CreateProfileSection::class)
            ->fillForm(['title' => '', 'slug' => ''])
            ->call('create')
            ->assertHasFormErrors(['title' => 'required', 'slug' => 'required']);

        Livewire::test(CreateProfileSection::class)
            ->fillForm([
                'title' => 'Judul Duplikat',
                'slug' => $existing->slug,
            ])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'unique']);
    }

    public function test_section_slug_must_be_url_safe(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateProfileSection::class)
            ->fillForm([
                'title' => 'Slug Salah Format',
                'slug' => 'Slug Salah / Format',
            ])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'regex']);
    }

    public function test_section_can_be_deleted_and_inactivated(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $section = ProfileSection::create([
            'title' => 'Seksi Sementara',
            'slug' => 'seksi-sementara',
            'content' => '<p>Konten.</p>',
            'sort_order' => 9,
            'is_active' => true,
        ]);

        $section->update(['is_active' => false]);
        $this->assertFalse($section->fresh()->is_active);

        $this->assertTrue(ProfileSectionResource::canDelete($section));

        $section->delete();
        $this->assertDatabaseMissing('profile_sections', ['id' => $section->id]);
    }

    public function test_non_privileged_role_cannot_manage_sections(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin_ppid_sop');
        $this->actingAs($user);

        $this->assertFalse(ProfileSectionResource::canViewAny());
        $this->assertFalse(ProfileSectionResource::canCreate());

        $this->get('/admin/profile-sections')->assertForbidden();
    }

    private function getSeededAdmin(): User
    {
        return User::where('email', 'admin@disdikpora.karangasemkab.go.id')->firstOrFail();
    }
}
