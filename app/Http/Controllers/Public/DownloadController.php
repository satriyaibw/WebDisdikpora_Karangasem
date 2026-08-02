<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\DownloadFile;

class DownloadController extends Controller
{
    public function index()
    {
        $groups = DownloadFile::published()
            ->get()
            ->groupBy(fn (DownloadFile $file) => $file->type);

        return view('pages.unduhan', compact('groups'));
    }
}
