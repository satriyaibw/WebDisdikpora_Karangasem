<?php

namespace Tests\Unit;

use App\Rules\ValidPdfFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ValidPdfFileTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    private const VALID_PDF = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF";

    /**
     * Kumpulkan pesan error yang dihasilkan rule terhadap sebuah nilai.
     *
     * @return array<int, string>
     */
    private function messages(ValidPdfFile $rule, mixed $value): array
    {
        $messages = [];

        $rule->validate('attachment_path', $value, function (string $message) use (&$messages): void {
            $messages[] = $message;
        });

        return $messages;
    }

    #[Test]
    public function it_accepts_a_valid_pdf_stored_on_public_disk(): void
    {
        Storage::disk('public')->put('lampiran/pengumuman/baik.pdf', self::VALID_PDF);

        $messages = $this->messages(new ValidPdfFile, 'lampiran/pengumuman/baik.pdf');

        $this->assertSame([], $messages);
    }

    #[Test]
    public function it_accepts_a_valid_pdf_from_temporary_upload(): void
    {
        FileUploadConfiguration::storage()->put(
            FileUploadConfiguration::path('asli.pdf'),
            self::VALID_PDF
        );

        $file = new TemporaryUploadedFile('asli.pdf', FileUploadConfiguration::disk());

        $messages = $this->messages(new ValidPdfFile, $file);

        $this->assertSame([], $messages);
    }

    #[Test]
    public function it_rejects_a_file_without_pdf_header(): void
    {
        Storage::disk('public')->put('lampiran/pengumuman/palsu.pdf', '<script>alert(1)</script>');

        $messages = $this->messages(new ValidPdfFile, 'lampiran/pengumuman/palsu.pdf');

        $this->assertNotSame([], $messages);
    }

    #[Test]
    public function it_rejects_a_polyglot_with_pdf_header_but_no_trailer(): void
    {
        Storage::disk('public')->put(
            'lampiran/pengumuman/polyglot.pdf',
            "%PDF-1.4\n1 0 obj\n<</Type /Catalog>>\nendobj\n<script>alert(1)</script>"
        );

        $messages = $this->messages(new ValidPdfFile, 'lampiran/pengumuman/polyglot.pdf');

        $this->assertNotSame([], $messages);
    }

    #[Test]
    public function it_accepts_a_pdf_with_binary_junk_before_the_header(): void
    {
        $content = str_repeat("\x00", 64).self::VALID_PDF;
        Storage::disk('public')->put('lampiran/pengumuman/junk.pdf', $content);

        $messages = $this->messages(new ValidPdfFile, 'lampiran/pengumuman/junk.pdf');

        $this->assertSame([], $messages);
    }

    #[Test]
    public function it_rejects_an_empty_file(): void
    {
        Storage::disk('public')->put('lampiran/pengumuman/kosong.pdf', '');

        $messages = $this->messages(new ValidPdfFile, 'lampiran/pengumuman/kosong.pdf');

        $this->assertNotSame([], $messages);
    }

    #[Test]
    public function it_rejects_paths_with_directory_traversal(): void
    {
        $messages = $this->messages(new ValidPdfFile, '../../app/Http/Kernel.php');

        $this->assertNotSame([], $messages);
    }

    #[Test]
    public function it_rejects_paths_that_do_not_exist_on_disk(): void
    {
        $messages = $this->messages(new ValidPdfFile, 'lampiran/pengumuman/tidak-ada.pdf');

        $this->assertNotSame([], $messages);
    }
}
