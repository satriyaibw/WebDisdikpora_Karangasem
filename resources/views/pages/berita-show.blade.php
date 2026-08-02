@extends('layouts.app')

@section('title', $news->title)

@section('metaDescription', Str::limit(strip_tags((string) $news->excerpt), 160))

@section('content')
    <article class="mx-auto max-w-4xl px-4 py-10 sm:px-6">
        <header>
            <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
                @if ($news->category)
                    <span class="rounded-full bg-brand-50 px-3 py-1 font-semibold text-brand-700">{{ $news->category->name }}</span>
                @endif
                <time datetime="{{ $news->published_at?->toDateString() }}">{{ $news->published_at?->translatedFormat('l, d F Y H:i') }}</time>
                @if ($news->author)
                    <span>Oleh: {{ $news->author->name }}</span>
                @endif
                <span class="flex items-center gap-1">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    {{ number_format($news->views_count) }}x dilihat
                </span>
            </div>
            <h1 class="mt-4 text-3xl font-bold leading-tight text-slate-900 sm:text-4xl">{{ $news->title }}</h1>
        </header>

        @if ($news->cover_image)
            <figure class="mt-8 overflow-hidden rounded-2xl shadow-sm ring-1 ring-slate-200">
                <img src="{{ Storage::url($news->cover_image) }}" alt="{{ $news->title }}" class="max-h-[480px] w-full object-cover">
            </figure>
        @endif

        <div class="prose prose-slate mt-8 max-w-none text-justify">
            {!! $news->content !!}
        </div>

        <nav class="mt-10 border-t border-slate-200 pt-6">
            <a href="{{ route('berita.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-600 hover:text-brand-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Daftar Berita
            </a>
        </nav>
    </article>

    @if ($related->isNotEmpty())
        <section class="border-t border-slate-200 bg-white">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <x-section-heading title="Berita Terkait" />
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $item)
                        <x-news-card :news="$item" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
