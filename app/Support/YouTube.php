<?php

namespace App\Support;

class YouTube
{
    /**
     * Ekstrak ID video YouTube dari URL: watch?v=, shorts/, youtu.be/.
     */
    public static function parseId(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $patterns = [
            '/[?&]v=([\w-]{11})/',
            '#youtu\.be/([\w-]{11})#',
            '#/(?:shorts|embed|live)/([\w-]{11})#',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * URL embed siap pakai di iframe.
     */
    public static function embedUrl(?string $url): ?string
    {
        $id = self::parseId($url);

        return $id ? 'https://www.youtube-nocookie.com/embed/'.$id : null;
    }
}
