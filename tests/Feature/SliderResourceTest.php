<?php

namespace Tests\Feature;

use App\Filament\Resources\SliderResource;
use App\Filament\Resources\SliderResource\Pages\CreateSlider;
use App\Models\Slider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SliderResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        Storage::fake('public');
    }

    public function test_creating_slider_via_form_persists_record_and_audit_log(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateSlider::class)
            ->fillForm([
                'title' => 'Banner Uji',
                'description' => 'Deskripsi CTA',
                'link' => 'https://disdikpora.karangasemkab.go.id/berita',
                'sort_order' => 2,
                'is_active' => true,
                'image' => UploadedFile::fake()->image('banner.jpg', 1200, 400),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $slider = Slider::where('title', 'Banner Uji')->firstOrFail();
        $this->assertEquals(2, $slider->sort_order);
        $this->assertTrue($slider->is_active);
        $this->assertStringEndsWith('.webp', $slider->image);
        $this->assertTrue(Storage::disk('public')->exists($slider->image));

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => Slider::class,
            'model_id' => $slider->id,
            'action' => 'create',
            'user_id' => $admin->id,
        ]);
    }

    public function test_sliders_are_ordered_by_sort_order(): void
    {
        Slider::create(['image' => 'images/slider/a.webp', 'title' => 'Kedua', 'sort_order' => 2]);
        Slider::create(['image' => 'images/slider/b.webp', 'title' => 'Pertama', 'sort_order' => 1]);

        $titles = Slider::query()->orderBy('sort_order')->pluck('title')->all();

        $this->assertEquals(['Pertama', 'Kedua'], $titles);
    }

    public function test_slider_link_rejects_non_http_schemes(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateSlider::class)
            ->fillForm([
                'title' => 'Banner Bahaya',
                'link' => 'javascript://alert(1)',
                'image' => UploadedFile::fake()->image('banner.jpg', 1200, 400),
            ])
            ->call('create')
            ->assertHasFormErrors(['link']);
    }

    public function test_slider_link_accepts_http_and_https_schemes(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateSlider::class)
            ->fillForm([
                'title' => 'Banner Aman',
                'link' => 'https://disdikpora.karangasemkab.go.id',
                'image' => UploadedFile::fake()->image('banner.jpg', 1200, 400),
            ])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    public function test_redaksi_role_can_manage_sliders(): void
    {
        $redaksi = User::factory()->create();
        $redaksi->assignRole('admin_redaksi_berita');
        $this->actingAs($redaksi);

        $this->assertTrue(SliderResource::canViewAny());
        $this->assertTrue(SliderResource::canCreate());
    }

    public function test_ppid_role_cannot_manage_sliders(): void
    {
        $ppid = User::factory()->create();
        $ppid->assignRole('admin_ppid_sop');
        $this->actingAs($ppid);

        $this->assertFalse(SliderResource::canViewAny());
        $this->assertFalse(SliderResource::canCreate());
    }

    private function getSeededAdmin(): User
    {
        return User::where('email', 'admin@disdikpora.karangasemkab.go.id')->firstOrFail();
    }
}
