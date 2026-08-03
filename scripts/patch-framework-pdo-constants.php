<?php

/**
 * Patch idempotent deprecation PHP 8.5 `PDO::MYSQL_ATTR_SSL_CA`.
 *
 * Laravel 11.x (EOL) masih memuat `vendor/laravel/framework/config/database.php`
 * sebagai base configuration di setiap boot, dan merujuk konstanta PDO yang
 * di-deprecate sejak PHP 8.5 — memicu warning deprecation di seluruh output
 * artisan/test pada PHP 8.5. Fix upstream (guard `defined('Pdo\Mysql::ATTR_SSL_CA')`)
 * baru tersedia di Laravel >= 12.40, sehingga diterapkan lewat script composer
 * ini (hook post-install-cmd / post-update-cmd).
 *
 * Idempotent: no-op bila baris sudah ter-patch; aman bila versi framework
 * di masa depan sudah memuat fix. Vendor di-gitignore — hasil patch
 * direproduksi otomatis pada setiap `composer install`/`composer update`.
 *
 * @var array<string, array<string, string>> Peta file (relatif root) => needle => replacement
 */
$patches = [
    'vendor/laravel/framework/config/database.php' => [
        "PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA')," => "(defined('Pdo\\Mysql::ATTR_SSL_CA') ? Pdo\\Mysql::ATTR_SSL_CA : PDO::MYSQL_ATTR_SSL_CA) => env('MYSQL_ATTR_SSL_CA'),",
    ],
    'vendor/laravel/framework/src/Illuminate/Database/Schema/MySqlSchemaState.php' => [
        '[\PDO::MYSQL_ATTR_SSL_CA]' => "[(defined('Pdo\\Mysql::ATTR_SSL_CA') ? \\Pdo\\Mysql::ATTR_SSL_CA : \\PDO::MYSQL_ATTR_SSL_CA)]",
    ],
];

$root = dirname(__DIR__);

foreach ($patches as $relative => $replacements) {
    $file = $root.'/'.$relative;

    if (! is_file($file)) {
        fwrite(STDERR, "Skip (file tidak ditemukan): {$relative}".PHP_EOL);

        continue;
    }

    $content = file_get_contents($file);

    if (! is_string($content)) {
        fwrite(STDERR, "Gagal membaca: {$relative}".PHP_EOL);

        continue;
    }

    $changed = false;

    foreach ($replacements as $needle => $replacement) {
        if (! str_contains($content, $needle)) {
            continue;
        }

        $content = str_replace($needle, $replacement, $content);
        $changed = true;
    }

    if ($changed) {
        file_put_contents($file, $content);
        echo "Patched: {$relative}".PHP_EOL;
    } else {
        echo "Up-to-date: {$relative}".PHP_EOL;
    }
}
