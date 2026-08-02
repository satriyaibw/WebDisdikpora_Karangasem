<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\News;

class NewsController extends Controller
{
    public function index()
    {
        $categories = Category::withCount(['news' => fn ($query) => $query->published()])
            ->orderBy('name')
            ->get();

        $activeCategory = request()->query('category');
        $search = request()->query('q');

        $news = News::published()
            ->with(['category', 'author'])
            ->when($activeCategory, fn ($query) => $query->whereHas(
                'category',
                fn ($q) => $q->where('slug', $activeCategory)
            ))
            ->when($search, fn ($query) => $query->where(fn ($q) => $q
                ->where('title', 'like', "%{$search}%")
                ->orWhere('excerpt', 'like', "%{$search}%")))
            ->orderByRaw('published_at IS NULL, published_at DESC')
            ->paginate(9)
            ->withQueryString();

        return view('pages.berita', compact('news', 'categories', 'activeCategory', 'search'));
    }

    public function show(News $news)
    {
        abort_unless($news->status === News::STATUS_PUBLISHED, 404);

        $news->load(['category', 'author']);
        $news->recordView();

        $related = News::published()
            ->whereKeyNot($news->getKey())
            ->when($news->category_id, fn ($query) => $query->where('category_id', $news->category_id))
            ->latest()
            ->limit(3)
            ->get();

        return view('pages.berita-show', compact('news', 'related'));
    }
}
