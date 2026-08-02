<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\DownloadFile;

class DownloadController extends Controller
{
    public function index()
    {
        $groups = DownloadFile::published()
            ->orderBy('type')
            ->orderBy('title')
            ->get()
            ->groupBy(fn (DownloadFile $file) => $file->type);

        $typeLabels = DownloadFile::typeOptions();

        return view('pages.unduhan', compact('groups', 'typeLabels'));
    }
}
