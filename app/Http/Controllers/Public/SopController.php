<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\SopDocument;
use Illuminate\Support\Facades\Storage;

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

        $sopDocument->fileExists = $sopDocument->file_path !== null
            && Storage::disk('public')->exists($sopDocument->file_path);
        $sopDocument->fileUrl = $sopDocument->fileExists
            ? Storage::disk('public')->url($sopDocument->file_path)
            : null;

        return view('pages.sop-show', compact('sopDocument'));
    }
}
