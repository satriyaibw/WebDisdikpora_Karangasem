<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ConfigureTrustedProxies
{
    public function handle(Request $request, Closure $next): mixed
    {
        // Dijalankan per-request sehingga env & config sudah pasti termuat
        // (bootstrap/app.php dieksekusi sebelum dotenv dibaca).
        // Hanya X-Forwarded-For & X-Forwarded-Proto yang dipercaya dari proxy;
        // Host/Port/Prefix header diabaikan untuk mencegah spoofing host.
        $request->setTrustedProxies(
            config('proxy.trusted_proxies'),
            Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PROTO,
        );

        return $next($request);
    }
}
