@extends('layouts.app')

@section('title', 'Galeri')

@section('metaDescription', 'Album foto kegiatan dan video dari Disdikpora Karangasem.')

@section('content')
    <x-page-hero title="Galeri" subtitle="Album foto kegiatan dan video dokumentasi" />

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <x-section-heading title="Album Foto" />

        @if ($albums->isEmpty())
            <x-empty-state message="Belum ada album foto." />
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($albums as $album)
                    <a href="{{ route('galeri.show', $album) }}" class="group overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:shadow-md">
                        <div class="relative">
                            @if ($album->photos()->exists())
                                <img
                                    src="{{ Storage::url($album->photos()->orderBy('sort_order')->first()->photo_path) }}"
                                    alt="{{ $album->title }}"
                                    loading="lazy"
                                    class="h-48 w-full object-cover transition duration-300 group-hover:scale-105"
                                >
                            @else
                                <div class="flex h-48 w-full items-center justify-center bg-brand-50">
                                    <svg class="h-12 w-12 text-brand-200" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                    </svg>
                                </div>
                            @endif
                            <span class="absolute bottom-3 right-3 rounded-full bg-slate-900/70 px-2.5 py-1 text-xs font-semibold text-white backdrop-blur">
                                {{ $album->photos_count }} foto
                            </span>
                        </div>
                        <div class="p-4">
                            <h3 class="font-bold text-slate-900 group-hover:text-brand-600">{{ $album->title }}</h3>
                            @if ($album->description)
                                <p class="mt-1 line-clamp-2 text-sm text-slate-500">{{ $album->description }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="mt-10">{{ $albums->links() }}</div>
        @endif

        @if ($videos->isNotEmpty())
            <div class="mt-16">
                <x-section-heading title="Video Dokumentasi" />
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($videos as $video)
                        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
                            <div class="aspect-video w-full">
                                <iframe
                                    src="https://www.youtube-nocookie.com/embed/{{ $video->youtube_id }}"
                                    title="{{ $video->title }}"
                                    loading="lazy"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen
                                    class="h-full w-full"
                                ></iframe>
                            </div>
                            <p class="px-4 py-3 text-sm font-semibold text-slate-800">{{ $video->title }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </section>
@endsection
