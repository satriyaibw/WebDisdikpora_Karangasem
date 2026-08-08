<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrustedProxyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_scheme_https_hanya_jika_proto_header_dari_proxy_terpercaya(): void
    {
        $resp = $this->withServerVariables([
            'REMOTE_ADDR' => '172.18.0.1',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ])->get('/kontak');

        $resp->assertOk();
        $this->assertSame('https', $this->app['request']->getScheme());
        $this->assertStringStartsWith('https://localhost/build/', $this->extractFirst($resp->getContent(), '#href="([^"]+app[^"]*\.css)"#'));
        $this->assertSame('https://localhost/kontak', $this->extractCanonical($resp->getContent()));
    }

    public function test_tanpa_header_scheme_tetap_http(): void
    {
        $resp = $this->get('/kontak');

        $resp->assertOk();
        $this->assertSame('http', $this->app['request']->getScheme());
        $this->assertStringStartsWith('http://localhost/build/', $this->extractFirst($resp->getContent(), '#href="([^"]+app[^"]*\.css)"#'));
        $this->assertSame('http://localhost/kontak', $this->extractCanonical($resp->getContent()));
    }

    public function test_proto_header_dari_ip_tidak_terpercaya_diabaikan(): void
    {
        $resp = $this->withServerVariables([
            'REMOTE_ADDR' => '1.2.3.4',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ])->get('/kontak');

        $resp->assertOk();
        $this->assertSame('http', $this->app['request']->getScheme());
        $this->assertStringStartsWith('http://localhost/build/', $this->extractFirst($resp->getContent(), '#href="([^"]+app[^"]*\.css)"#'));
        $this->assertSame('http://localhost/kontak', $this->extractCanonical($resp->getContent()));
    }

    public function test_x_forwarded_host_dari_proxy_tidak_mengubah_host(): void
    {
        $resp = $this->withServerVariables([
            'REMOTE_ADDR' => '172.18.0.1',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_HOST' => 'evil.example.com',
        ])->get('/kontak');

        $resp->assertOk();
        $this->assertSame('https://localhost/kontak', $this->extractCanonical($resp->getContent()));
        $this->assertStringNotContainsString('evil.example.com', $resp->getContent());
    }

    private function extractCanonical(string $html): string
    {
        return trim((string) preg_match('#<link rel="canonical" href="([^"]+)"#', $html, $m) ? $m[1] : '');
    }

    private function extractFirst(string $html, string $pattern): ?string
    {
        return preg_match($pattern, $html, $m) ? $m[1] : null;
    }
}
