<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\News;
use App\Support\PublicCache;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $categories = PublicCache::remember(PublicCache::NEWS_CATEGORIES, fn () => Category::withCount(['news' => fn ($query) => $query->published()])
            ->orderBy('name')
            ->get(), [PublicCache::TAG_NEWS]);

        $activeCategory = $request->query('category');
        $search = $request->query('q');

        $listKey = PublicCache::keyFor('news.list', [
            'page' => $request->input('page', 1),
            'category' => (string) $activeCategory,
            'search' => (string) $search,
        ]);

        $news = PublicCache::remember($listKey, fn () => News::published()
            ->with(['category', 'author'])
            ->when($activeCategory, fn ($query) => $query->whereHas(
                'category',
                fn ($q) => $q->where('slug', $activeCategory)
            ))
            ->when($search, fn ($query) => $query->where(fn ($q) => $q
                ->where('title', 'like', '%'.escapeLike($search).'%')
                ->orWhere('excerpt', 'like', '%'.escapeLike($search).'%')))
            ->orderByRaw('published_at IS NULL, published_at DESC')
            ->paginate(9)
            ->withQueryString(), [PublicCache::TAG_NEWS]);

        return view('pages.berita', compact('news', 'categories', 'activeCategory', 'search'));
    }

    public function show(News $news)
    {
        abort_unless($news->isPublishedForPublic(), 404);

        $news->load(['category', 'author']);
        $news->recordView();

        $related = PublicCache::remember('news.related.'.$news->getKey(), fn () => News::published()
            ->whereKeyNot($news->getKey())
            ->when($news->category_id, fn ($query) => $query->where('category_id', $news->category_id))
            ->orderByRaw('published_at IS NULL, published_at DESC')
            ->limit(3)
            ->get(), [PublicCache::TAG_NEWS]);

        return view('pages.berita-show', compact('news', 'related'));
    }
}
