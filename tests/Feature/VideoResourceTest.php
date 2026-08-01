<?php

namespace Tests\Feature;

use App\Filament\Resources\VideoResource;
use App\Filament\Resources\VideoResource\Pages\CreateVideo;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VideoResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_creating_video_via_form_persists_record_and_audit_log(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateVideo::class)
            ->fillForm([
                'title' => 'Video Uji',
                'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'description' => 'Deskripsi video',
                'status' => Video::STATUS_PUBLISHED,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $video = Video::where('title', 'Video Uji')->firstOrFail();
        $this->assertEquals('https://www.youtube.com/watch?v=dQw4w9WgXcQ', $video->youtube_url);

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => Video::class,
            'model_id' => $video->id,
            'action' => 'create',
            'user_id' => $admin->id,
        ]);
    }

    public function test_invalid_youtube_url_is_rejected(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateVideo::class)
            ->fillForm([
                'title' => 'Video Invalid',
                'youtube_url' => 'https://vimeo.com/12345',
                'status' => Video::STATUS_DRAFT,
            ])
            ->call('create')
            ->assertHasFormErrors(['youtube_url']);
    }

    public function test_redaksi_role_can_manage_videos(): void
    {
        $redaksi = User::factory()->create();
        $redaksi->assignRole('admin_redaksi_berita');
        $this->actingAs($redaksi);

        $this->assertTrue(VideoResource::canViewAny());
        $this->assertTrue(VideoResource::canCreate());
    }

    public function test_ppid_role_cannot_manage_videos(): void
    {
        $ppid = User::factory()->create();
        $ppid->assignRole('admin_ppid_sop');
        $this->actingAs($ppid);

        $this->assertFalse(VideoResource::canViewAny());
        $this->assertFalse(VideoResource::canCreate());
    }

    private function getSeededAdmin(): User
    {
        return User::where('email', 'admin@disdikpora.karangasemkab.go.id')->firstOrFail();
    }
}
