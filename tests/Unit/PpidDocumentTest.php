<?php

namespace Tests\Unit;

use App\Models\PpidDocument;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PpidDocumentTest extends TestCase
{
    #[Test]
    public function format_file_size_returns_human_readable_values(): void
    {
        $this->assertSame('0 B', PpidDocument::formatFileSize(0));
        $this->assertSame('512 B', PpidDocument::formatFileSize(512));
        $this->assertSame('1.0 KB', PpidDocument::formatFileSize(1024));
        $this->assertSame('1.5 KB', PpidDocument::formatFileSize(1536));
        $this->assertSame('1.0 MB', PpidDocument::formatFileSize(1048576));
        $this->assertSame('10.0 MB', PpidDocument::formatFileSize(10485760));
        $this->assertSame('-', PpidDocument::formatFileSize(null));
        $this->assertSame('-', PpidDocument::formatFileSize(-1));
    }
}
