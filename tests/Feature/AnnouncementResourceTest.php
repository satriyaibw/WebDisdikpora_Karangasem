<?php

namespace Tests\Feature;

use App\Filament\Resources\AnnouncementResource;
use App\Filament\Resources\AnnouncementResource\Pages\CreateAnnouncement;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AnnouncementResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        Storage::fake('public');
    }

    public function test_creating_announcement_via_form_persists_record_and_audit_log(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateAnnouncement::class)
            ->fillForm([
                'title' => 'Pengumuman Uji',
                'content' => '<p>Isi pengumuman</p>',
                'announcement_number' => '800/001/DISDIKPORA',
                'announcement_date' => '2026-08-01',
                'is_important' => true,
                'status' => Announcement::STATUS_PUBLISHED,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $announcement = Announcement::where('title', 'Pengumuman Uji')->firstOrFail();
        $this->assertTrue($announcement->is_important);
        $this->assertEquals(Announcement::STATUS_PUBLISHED, $announcement->status);

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => Announcement::class,
            'model_id' => $announcement->id,
            'action' => 'create',
            'user_id' => $admin->id,
        ]);
    }

    public function test_pdf_attachment_is_accepted_and_stored(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateAnnouncement::class)
            ->fillForm([
                'title' => 'Pengumuman Lampiran',
                'content' => '<p>Isi</p>',
                'status' => Announcement::STATUS_DRAFT,
                'attachment_path' => UploadedFile::fake()->createWithContent('lampiran.pdf', "%PDF-1.4\n1 0 obj\n%%EOF"),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $announcement = Announcement::where('title', 'Pengumuman Lampiran')->firstOrFail();

        $this->assertNotNull($announcement->attachment_path);
        $this->assertTrue(Storage::disk('public')->exists($announcement->attachment_path));
        $this->assertStringEndsWith('.pdf', $announcement->attachment_path);
    }

    public function test_non_pdf_attachment_is_rejected(): void
    {
        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateAnnouncement::class)
            ->fillForm([
                'title' => 'Pengumuman Tanpa PDF',
                'content' => '<p>Isi</p>',
                'status' => Announcement::STATUS_DRAFT,
                'attachment_path' => UploadedFile::fake()->create('dokumen.txt', 100),
            ])
            ->call('create')
            ->assertHasFormErrors(['attachment_path']);
    }

    public function test_attachment_filename_is_sanitized_against_path_traversal(): void
    {
        $name = AnnouncementResource::safeStoredFileName('../../evil.pdf');

        $this->assertStringNotContainsString('/', $name);
        $this->assertStringNotContainsString('..', $name);
        $this->assertStringEndsWith('.pdf', $name);

        $admin = $this->getSeededAdmin();
        $this->actingAs($admin);

        Livewire::test(CreateAnnouncement::class)
            ->fillForm([
                'title' => 'Pengumuman Aman',
                'content' => '<p>Isi</p>',
                'status' => Announcement::STATUS_DRAFT,
                'attachment_path' => UploadedFile::fake()->createWithContent('../../evil.pdf', "%PDF-1.4\n%%EOF"),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $announcement = Announcement::where('title', 'Pengumuman Aman')->firstOrFail();

        $storedName = basename($announcement->attachment_path);

        $this->assertStringNotContainsString('/', $storedName);
        $this->assertStringNotContainsString('..', $storedName);
        $this->assertStringEndsWith('.pdf', $storedName);
        $this->assertTrue(Storage::disk('public')->exists($announcement->attachment_path));
    }

    public function test_deleting_announcement_removes_attachment_from_disk(): void
    {
        $admin = $this->getSeededAdmin();

        $path = 'lampiran/pengumuman/berkas.pdf';
        Storage::disk('public')->put($path, 'data');

        $announcement = Announcement::create([
            'title' => 'Hapus Lampiran',
            'content' => '<p>Isi</p>',
            'status' => Announcement::STATUS_DRAFT,
            'attachment_path' => $path,
        ]);

        $announcement->delete();

        $this->assertFalse(Storage::disk('public')->exists($path));
    }

    public function test_redaksi_role_can_manage_announcements(): void
    {
        $redaksi = User::factory()->create();
        $redaksi->assignRole('admin_redaksi_berita');
        $this->actingAs($redaksi);

        $this->assertTrue(AnnouncementResource::canViewAny());
        $this->assertTrue(AnnouncementResource::canCreate());
    }

    public function test_ppid_role_cannot_manage_announcements(): void
    {
        $ppid = User::factory()->create();
        $ppid->assignRole('admin_ppid_sop');
        $this->actingAs($ppid);

        $this->assertFalse(AnnouncementResource::canViewAny());
        $this->assertFalse(AnnouncementResource::canCreate());
    }

    private function getSeededAdmin(): User
    {
        return User::where('email', 'admin@disdikpora.karangasemkab.go.id')->firstOrFail();
    }
}
