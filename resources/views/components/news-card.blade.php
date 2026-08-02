@props(['news' => null])

@php($coverUrl = public_url_if_exists($news->cover_image))

<article class="group flex h-full flex-col overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:shadow-md">
    <a href="{{ route('berita.show', $news->slug) }}" class="block">
        @if ($coverUrl)
            <img
                src="{{ $coverUrl }}"
                alt="{{ $news->title }}"
                loading="lazy"
                class="h-44 w-full object-cover transition duration-300 group-hover:scale-105"
            >
        @else
            <div class="flex h-44 w-full items-center justify-center bg-brand-50">
                <svg class="h-10 w-10 text-brand-200" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 01-2.25 2.25M16.5 7.5V18a2.25 2.25 0 002.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 002.25 2.25h13.5M6 7.5h3v3H6v-3z" />
                </svg>
            </div>
        @endif
    </a>
    <div class="flex flex-1 flex-col p-4">
        <div class="flex items-center gap-2 text-xs text-slate-500">
            @if ($news->category)
                <span class="rounded-full bg-brand-50 px-2 py-0.5 font-semibold text-brand-700">{{ $news->category->name }}</span>
            @endif
            <time datetime="{{ $news->published_at?->toDateString() }}">{{ $news->published_at?->translatedFormat('d F Y') }}</time>
        </div>
        <h3 class="mt-2 line-clamp-2 text-base font-bold text-slate-900 group-hover:text-brand-600">
            <a href="{{ route('berita.show', $news->slug) }}">{{ $news->title }}</a>
        </h3>
        <p class="mt-2 line-clamp-3 flex-1 text-sm text-slate-600">{{ Str::limit(strip_tags((string) $news->excerpt), 120) }}</p>
        <a href="{{ route('berita.show', $news->slug) }}" class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-brand-600 hover:text-brand-700">
            Baca Selengkapnya
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
            </svg>
        </a>
    </div>
</article>
