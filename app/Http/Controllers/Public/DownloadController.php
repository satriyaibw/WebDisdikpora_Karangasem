<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\DownloadCategory;
use App\Models\DownloadFile;
use App\Support\PublicCache;

class DownloadController extends Controller
{
    public function index()
    {
        $groups = PublicCache::remember(PublicCache::DOWNLOADS_GROUPS, fn () => DownloadCategory::query()
            ->with(['downloadFiles' => fn ($query) => $query->published()->orderBy('title')])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->filter(fn (DownloadCategory $category) => $category->downloadFiles->isNotEmpty()), [PublicCache::TAG_DOWNLOADS]);

        return view('pages.unduhan', compact('groups'));
    }

    public function download(DownloadFile $downloadFile)
    {
        abort_unless($downloadFile->status === DownloadFile::STATUS_PUBLISHED, 404);

        return public_download_response($downloadFile->file_path);
    }
}
