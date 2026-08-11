@extends('layouts.app')

@section('title', 'Berita')

@section('metaDescription', 'Kumpulan berita dan informasi terkini dari Disdikpora Karangasem.')

@section('content')
    <x-page-hero title="Berita" subtitle="Berita dan informasi terkini dari {{ settings('site.short_name', '') }}" />

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <h2 class="sr-only">Daftar Berita</h2>
        <div class="mb-8 flex flex-wrap items-center gap-3">
            <form action="{{ route('berita.index') }}" method="GET" class="flex w-full max-w-sm items-center gap-2" role="search">
                <input
                    type="search"
                    name="q"
                    value="{{ $search }}"
                    placeholder="Cari berita..."
                    class="w-full rounded-lg border-slate-300 py-2 pl-4 pr-10 text-sm focus:border-brand-500 focus:ring-brand-500"
                >
                <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-600">Cari</button>
            </form>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('berita.index') }}" class="rounded-full px-4 py-1.5 text-sm font-semibold transition {{ ! $activeCategory ? 'bg-brand-500 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-300 hover:bg-brand-50' }}">Semua</a>
                @foreach ($categories as $category)
                    <a href="{{ route('berita.index', ['category' => $category->slug]) }}" class="rounded-full px-4 py-1.5 text-sm font-semibold transition {{ $activeCategory === $category->slug ? 'bg-brand-500 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-300 hover:bg-brand-50' }}">
                        {{ $category->name }}
                        <span class="ml-1 text-xs opacity-70">{{ $category->news_count }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        @if ($news->isEmpty())
            <x-empty-state message="Tidak ada berita yang cocok." />
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($news as $item)
                    <x-news-card :news="$item" />
                @endforeach
            </div>
            <div class="mt-10">{{ $news->links() }}</div>
        @endif
    </section>
@endsection
