<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PpidDocument;

class PpidController extends Controller
{
    public function index()
    {
        return view('pages.ppid');
    }

    public function download(PpidDocument $ppidDocument)
    {
        abort_unless($ppidDocument->status === PpidDocument::STATUS_PUBLISHED, 404);

        return public_download_response($ppidDocument->file_path);
    }
}
