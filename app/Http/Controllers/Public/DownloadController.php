<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\DownloadCategory;

class DownloadController extends Controller
{
    public function index()
    {
        $groups = DownloadCategory::query()
            ->with(['downloadFiles' => fn ($query) => $query->published()->orderBy('title')])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->filter(fn (DownloadCategory $category) => $category->downloadFiles->isNotEmpty());

        return view('pages.unduhan', compact('groups'));
    }
}
