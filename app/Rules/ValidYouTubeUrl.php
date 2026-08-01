<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validasi tautan YouTube (MasterPlan 3.5).
 *
 * Menerima format umum:
 * - https://www.youtube.com/watch?v=ID
 * - https://youtu.be/ID
 * - https://www.youtube.com/embed/ID
 * - https://www.youtube.com/shorts/ID
 */
class ValidYouTubeUrl implements ValidationRule
{
    /**
     * Host yang diizinkan (termasuk varian nocookie untuk embed).
     */
    private const ALLOWED_HOSTS = [
        'youtube.com',
        'www.youtube.com',
        'youtu.be',
        'm.youtube.com',
        'youtube-nocookie.com',
        'www.youtube-nocookie.com',
    ];

    /**
     * Format ID video YouTube (11 karakter: huruf, angka, `-`, `_`).
     */
    private const VIDEO_ID_PATTERN = '/^[A-Za-z0-9_-]{11}$/';

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('Tautan YouTube tidak valid.');

            return;
        }

        $host = strtolower((string) parse_url($value, PHP_URL_HOST));

        if (! in_array($host, self::ALLOWED_HOSTS, true)) {
            $fail('Tautan harus berasal dari YouTube (youtube.com / youtu.be).');

            return;
        }

        $videoId = static::extractVideoId($value);

        if ($videoId === null || preg_match(self::VIDEO_ID_PATTERN, $videoId) !== 1) {
            $fail('Tautan YouTube tidak memuat ID video yang valid.');
        }
    }

    /**
     * Ambil ID video dari berbagai format tautan YouTube.
     */
    public static function extractVideoId(string $url): ?string
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        if (in_array($host, self::ALLOWED_HOSTS, true)) {
            $path = (string) parse_url($url, PHP_URL_PATH);
            $query = parse_url($url, PHP_URL_QUERY);

            parse_str((string) $query, $params);

            if (isset($params['v']) && is_string($params['v'])) {
                return $params['v'] !== '' ? $params['v'] : null;
            }

            $segments = array_values(array_filter(explode('/', $path)));

            if ($host === 'youtu.be' && isset($segments[0])) {
                return $segments[0];
            }

            if (isset($segments[1]) && in_array($segments[0], ['embed', 'shorts', 'live'], true)) {
                return $segments[1];
            }
        }

        return null;
    }
}
