<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\SopDocument;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SopController extends Controller
{
    public function index()
    {
        return view('pages.sop');
    }

    public function show(SopDocument $sopDocument)
    {
        abort_unless($sopDocument->status === SopDocument::STATUS_PUBLISHED, 404);

        $sopDocument->load('bidang');

        return view('pages.sop-show', compact('sopDocument'));
    }

    public function download(SopDocument $sopDocument)
    {
        abort_unless($sopDocument->status === SopDocument::STATUS_PUBLISHED, 404);

        return gated_download_response($sopDocument->file_path);
    }

    public function preview(SopDocument $sopDocument)
    {
        abort_unless($sopDocument->status === SopDocument::STATUS_PUBLISHED, 404);

        return gated_inline_response($sopDocument->file_path);
    }

    /**
     * Pratinjau inline untuk iframe (best practice).
     *
     * Menggantikan direct Storage::url() yang rapuh saat APP_URL stale
     * (Cloudflare quick tunnel hostname berganti tiap run). Route ini memakai
     * host dari request saat ini (via TrustedProxies) sehingga selalu same-origin.
     */
    public function preview(SopDocument $sopDocument): BinaryFileResponse
    {
        abort_unless($sopDocument->status === SopDocument::STATUS_PUBLISHED, 404);

        if (! $sopDocument->file_path || ! Storage::disk('public')->exists($sopDocument->file_path)) {
            abort(404);
        }

        $absolutePath = Storage::disk('public')->path($sopDocument->file_path);
        $fileName = basename($sopDocument->file_path);

        $response = response()->file($absolutePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$fileName.'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);

        // Privat: jangan cache bersama di CDN/proxy, tapi izinkan cache privat 1 jam
        $response->setPrivate();
        $response->headers->set('Cache-Control', 'private, max-age=3600, must-revalidate');

        return $response;
    }
}
