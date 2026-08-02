<?php

namespace Tests\Feature;

use App\Filament\Resources\OfficialResource;
use App\Filament\Resources\OfficialResource\Pages\CreateOfficial;
use App\Filament\Resources\OfficialResource\Pages\EditOfficial;
use App\Models\Official;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OfficialResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_creating_official_via_form_persists_record_and_audit_log(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateOfficial::class)
            ->fillForm([
                'jabatan' => 'Kepala Bidang Perencanaan',
                'nama' => 'Ni Wayan Sari, S.E.',
                'nip' => '197501012000031002',
                'sort_order' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $official = Official::where('jabatan', 'Kepala Bidang Perencanaan')->firstOrFail();
        $this->assertEquals('Ni Wayan Sari, S.E.', $official->nama);

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => Official::class,
            'model_id' => $official->id,
            'action' => 'create',
            'user_id' => $admin->id,
        ]);
    }

    public function test_jabatan_is_required_and_unique(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $existing = Official::firstOrFail();

        Livewire::test(CreateOfficial::class)
            ->fillForm([
                'jabatan' => $existing->jabatan,
            ])
            ->call('create')
            ->assertHasFormErrors(['jabatan' => 'unique']);

        Livewire::test(CreateOfficial::class)
            ->fillForm([
                'jabatan' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['jabatan' => 'required']);
    }

    public function test_official_can_be_updated_with_parent_and_deleted(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $parent = Official::firstOrFail();
        $official = Official::create([
            'jabatan' => 'Kepala Sub Bagian Umum',
            'nama' => '-',
            'is_active' => true,
        ]);

        Livewire::test(EditOfficial::class, ['record' => $official->getRouteKey()])
            ->fillForm([
                'jabatan' => 'Kepala Sub Bagian Umum',
                'nama' => 'I Komang Adi, S.Sos.',
                'parent_id' => $parent->id,
                'sort_order' => 3,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $official->refresh();
        $this->assertEquals('I Komang Adi, S.Sos.', $official->nama);
        $this->assertEquals($parent->id, $official->parent_id);

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => Official::class,
            'model_id' => $official->id,
            'action' => 'update',
            'user_id' => $admin->id,
        ]);

        $official->delete();
        $this->assertDatabaseMissing('officials', ['id' => $official->id]);
    }

    public function test_super_admin_can_manage_officials(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $this->assertTrue(OfficialResource::canViewAny());
        $this->assertTrue(OfficialResource::canCreate());
        $this->assertTrue(OfficialResource::canEdit(Official::first()));
        $this->assertTrue(OfficialResource::canDelete(Official::first()));
    }

    public function test_non_privileged_role_cannot_manage_officials(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin_ppid_sop');
        $this->actingAs($user);

        $this->assertFalse(OfficialResource::canViewAny());
        $this->assertFalse(OfficialResource::canCreate());
        $this->assertFalse(OfficialResource::canDelete(Official::first()));

        $this->get('/admin/officials')->assertForbidden();
    }

    private function getSeededAdmin(): User
    {
        return User::where('email', 'admin@disdikpora.karangasemkab.go.id')->firstOrFail();
    }
}
