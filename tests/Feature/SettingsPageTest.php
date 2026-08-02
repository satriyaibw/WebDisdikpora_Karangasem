<?php

namespace Tests\Feature;

use App\Filament\Pages\Pengaturan;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_pengaturan_page_loads_with_seeded_values(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $this->get('/admin/pengaturan')->assertOk();

        Livewire::test(Pengaturan::class)
            ->assertFormSet([
                'site.name' => 'Dinas Pendidikan, Kepemudaan dan Olahraga Kabupaten Karangasem',
                'site.tagline' => 'Melayani dengan Hati',
            ]);
    }

    public function test_saving_form_persists_settings_and_flushes_cache(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(Pengaturan::class)
            ->fillForm([
                'site.name' => 'Dinas Pendidikan Kabupaten Karangasem',
                'site.short_name' => 'Disdikpora Karangasem',
                'site.tagline' => 'Melayani dengan Hati',
                'site.address' => 'Jl. Ngurah Rai, Amlapura',
                'site.email' => 'info@disdikpora.karangasemkab.go.id',
                'site.phone' => '(0363) 21034',
                'profile.kadis_name' => 'Drs. I Wayan Suparta, M.M.',
                'profile.sekretariat_name' => 'I Gede Sudarma, S.Pd., M.Pd.',
                'profile.welcome' => '<p>Sambutan baru.</p>',
                'profile.vision' => '<p>Visi baru.</p>',
                'profile.mission' => '<ol><li>Misi baru.</li></ol>',
                'profile.duties' => '<p>Tupoksi baru.</p>',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertFalse(Cache::has('settings'));

        $this->assertSame('Dinas Pendidikan Kabupaten Karangasem', settings('site.name'));
        $this->assertSame('Drs. I Wayan Suparta, M.M.', settings('profile.kadis_name'));
        $this->assertSame('profile', Setting::where('key', 'profile.kadis_name')->firstOrFail()->group);
        $this->assertSame('contact', Setting::where('key', 'site.phone')->firstOrFail()->group);
        $this->assertSame('general', Setting::where('key', 'site.tagline')->firstOrFail()->group);
    }

    public function test_save_button_is_associated_with_the_form(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(Pengaturan::class)
            ->assertSee('Simpan Pengaturan')
            ->assertSeeHtml('form="form"');
    }

    public function test_pengaturan_page_not_accessible_without_permission(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin_ppid_sop');
        $this->actingAs($user);

        $this->get('/admin/pengaturan')->assertForbidden();
    }

    private function getSeededAdmin(): User
    {
        return User::where('email', 'admin@disdikpora.karangasemkab.go.id')->firstOrFail();
    }
}
