<?php

namespace Tests\Feature;

use App\Filament\Resources\AlbumResource;
use App\Filament\Resources\AlbumResource\Pages\CreateAlbum;
use App\Models\Album;
use App\Models\AlbumPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AlbumResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        Storage::fake('public');
    }

    public function test_creating_album_with_photos_via_form_persists_records_and_audit_logs(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateAlbum::class)
            ->fillForm([
                'title' => 'Album Kegiatan Uji',
                'description' => 'Dokumentasi kegiatan',
                'photos' => [
                    ['photo_path' => [UploadedFile::fake()->image('foto1.jpg', 800, 600)], 'caption' => 'Foto pembukaan'],
                    ['photo_path' => [UploadedFile::fake()->image('foto2.png', 400, 300)], 'caption' => 'Foto penutupan'],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $album = Album::where('title', 'Album Kegiatan Uji')->firstOrFail();
        $this->assertCount(2, $album->photos);

        foreach ($album->photos as $photo) {
            $this->assertStringEndsWith('.webp', $photo->photo_path);
            $this->assertTrue(Storage::disk('public')->exists($photo->photo_path));
        }

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => Album::class,
            'model_id' => $album->id,
            'action' => 'create',
            'user_id' => $admin->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => AlbumPhoto::class,
            'action' => 'create',
            'user_id' => $admin->id,
        ]);
    }

    public function test_deleting_album_removes_photo_files_from_disk(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateAlbum::class)
            ->fillForm([
                'title' => 'Album Akan Dihapus',
                'photos' => [
                    ['photo_path' => [UploadedFile::fake()->image('foto.jpg', 800, 600)], 'caption' => null],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $album = Album::where('title', 'Album Akan Dihapus')->firstOrFail();
        $path = $album->photos->first()->photo_path;

        $this->assertTrue(Storage::disk('public')->exists($path));

        $album->delete();

        $this->assertFalse(Storage::disk('public')->exists($path));
        $this->assertDatabaseMissing('album_photos', ['album_id' => $album->id]);
    }

    public function test_redaksi_role_can_manage_albums(): void
    {
        $redaksi = User::factory()->create();
        $redaksi->assignRole('admin_redaksi_berita');
        $this->actingAs($redaksi);

        $this->assertTrue(AlbumResource::canViewAny());
        $this->assertTrue(AlbumResource::canCreate());
    }

    public function test_ppid_role_cannot_manage_albums(): void
    {
        $ppid = User::factory()->create();
        $ppid->assignRole('admin_ppid_sop');
        $this->actingAs($ppid);

        $this->assertFalse(AlbumResource::canViewAny());
        $this->assertFalse(AlbumResource::canCreate());
    }

    private function getSeededAdmin(): User
    {
        return User::where('email', 'admin@disdikpora.karangasemkab.go.id')->firstOrFail();
    }
}
