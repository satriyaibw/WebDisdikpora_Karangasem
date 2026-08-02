<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\SopDocument;

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
}
