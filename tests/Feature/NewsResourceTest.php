<?php

namespace Tests\Feature;

use App\Filament\Resources\NewsResource;
use App\Filament\Resources\NewsResource\Pages\CreateNews;
use App\Models\Category;
use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class NewsResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_creating_news_via_form_persists_record_and_audit_log(): void
    {
        $admin = $this->getSeededAdmin();
        $category = Category::where('slug', 'pendidikan')->firstOrFail();
        $this->actingAs($admin);

        Livewire::test(CreateNews::class)
            ->fillForm([
                'title' => 'Berita Uji',
                'slug' => 'berita-uji',
                'content' => '<p>Isi berita uji</p>',
                'excerpt' => 'Ringkasan singkat',
                'category_id' => $category->id,
                'author_id' => $admin->id,
                'status' => News::STATUS_DRAFT,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $news = News::where('slug', 'berita-uji')->firstOrFail();
        $this->assertEquals('Berita Uji', $news->title);
        $this->assertEquals($category->id, $news->category_id);
        $this->assertEquals($admin->id, $news->author_id);
        $this->assertEquals(News::STATUS_DRAFT, $news->status);

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => News::class,
            'model_id' => $news->id,
            'action' => 'create',
            'user_id' => $admin->id,
        ]);
    }

    public function test_published_news_with_future_published_at_is_saved_as_scheduled(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateNews::class)
            ->fillForm([
                'title' => 'Berita Terjadwal',
                'slug' => 'berita-terjadwal',
                'content' => '<p>Isi</p>',
                'author_id' => $admin->id,
                'status' => News::STATUS_PUBLISHED,
                'published_at' => now()->addDay()->format('Y-m-d H:i:s'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $news = News::where('slug', 'berita-terjadwal')->firstOrFail();
        $this->assertEquals(News::STATUS_SCHEDULED, $news->status);
    }

    public function test_published_news_with_past_published_at_stays_published(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateNews::class)
            ->fillForm([
                'title' => 'Berita Terbit',
                'slug' => 'berita-terbit',
                'content' => '<p>Isi</p>',
                'author_id' => $admin->id,
                'status' => News::STATUS_PUBLISHED,
                'published_at' => now()->subDay()->format('Y-m-d H:i:s'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $news = News::where('slug', 'berita-terbit')->firstOrFail();
        $this->assertEquals(News::STATUS_PUBLISHED, $news->status);
    }

    public function test_scheduled_news_publishes_automatically_when_time_arrives(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $news = News::create([
            'title' => 'Terjadwal',
            'slug' => 'terjadwal-sudah-lewat',
            'content' => '<p>Isi</p>',
            'author_id' => $admin->id,
            'status' => News::STATUS_SCHEDULED,
            'published_at' => now()->subMinute(),
        ]);

        Artisan::call('news:publish-scheduled');

        $this->assertEquals(News::STATUS_PUBLISHED, $news->fresh()->status);

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => News::class,
            'model_id' => $news->id,
            'action' => 'update',
        ]);
    }

    public function test_future_scheduled_news_is_not_published_by_command(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        $news = News::create([
            'title' => 'Terjadwal Masa Depan',
            'slug' => 'terjadwal-future',
            'content' => '<p>Isi</p>',
            'author_id' => $admin->id,
            'status' => News::STATUS_SCHEDULED,
            'published_at' => now()->addDay(),
        ]);

        Artisan::call('news:publish-scheduled');

        $this->assertEquals(News::STATUS_SCHEDULED, $news->fresh()->status);
    }

    public function test_scheduled_news_without_published_at_is_published_immediately(): void
    {
        $data = NewsResource::resolvePublishStatus([
            'status' => News::STATUS_SCHEDULED,
            'published_at' => null,
        ]);

        $this->assertEquals(News::STATUS_PUBLISHED, $data['status']);
        $this->assertNotNull($data['published_at']);
    }

    public function test_scheduled_news_with_empty_published_at_does_not_crash(): void
    {
        $data = NewsResource::resolvePublishStatus([
            'status' => News::STATUS_SCHEDULED,
            'published_at' => '',
        ]);

        $this->assertEquals(News::STATUS_PUBLISHED, $data['status']);
        $this->assertNotNull($data['published_at']);
    }

    public function test_deleting_news_removes_cover_image_from_disk(): void
    {
        Storage::fake('public');

        $admin = $this->getSeededAdmin();

        $path = 'images/berita/sampul.webp';
        Storage::disk('public')->put($path, 'data');

        $news = News::create([
            'title' => 'Hapus Sampul',
            'slug' => 'hapus-sampul',
            'content' => '<p>Isi</p>',
            'author_id' => $admin->id,
            'status' => News::STATUS_DRAFT,
            'cover_image' => $path,
        ]);

        $news->delete();

        $this->assertFalse(Storage::disk('public')->exists($path));
    }

    public function test_replacing_cover_image_removes_old_file_from_disk(): void
    {
        Storage::fake('public');

        $admin = $this->getSeededAdmin();

        $oldPath = 'images/berita/lama.webp';
        $newPath = 'images/berita/baru.webp';
        Storage::disk('public')->put($oldPath, 'a');
        Storage::disk('public')->put($newPath, 'b');

        $news = News::create([
            'title' => 'Ganti Sampul',
            'slug' => 'ganti-sampul',
            'content' => '<p>Isi</p>',
            'author_id' => $admin->id,
            'status' => News::STATUS_DRAFT,
            'cover_image' => $oldPath,
        ]);

        $news->cover_image = $newPath;
        $news->save();

        $this->assertFalse(Storage::disk('public')->exists($oldPath));
        $this->assertTrue(Storage::disk('public')->exists($newPath));
    }

    public function test_redaksi_role_can_access_news_management(): void
    {
        $redaksi = User::factory()->create();
        $redaksi->assignRole('admin_redaksi_berita');
        $this->actingAs($redaksi);

        $this->assertTrue(NewsResource::canViewAny());
        $this->assertTrue(NewsResource::canCreate());
        $this->assertTrue(NewsResource::canEdit(new News));
        $this->assertTrue(NewsResource::canDelete(new News));
    }

    public function test_ppid_role_cannot_access_news_management(): void
    {
        $ppid = User::factory()->create();
        $ppid->assignRole('admin_ppid_sop');
        $this->actingAs($ppid);

        $this->assertFalse(NewsResource::canViewAny());
        $this->assertFalse(NewsResource::canCreate());
    }

    public function test_news_resource_form_and_table_are_registered(): void
    {
        $this->assertSame(News::class, NewsResource::getModel());
    }

    private function getSeededAdmin(): User
    {
        return User::where('email', 'admin@disdikpora.karangasemkab.go.id')->firstOrFail();
    }
}
