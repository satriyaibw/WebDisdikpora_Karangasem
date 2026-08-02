@extends('layouts.app')

@section('title', $album->title)

@section('metaDescription', Str::limit(strip_tags((string) $album->description), 160))

@push('meta')
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $album->title }}">
    @if ($firstPhoto = $album->photos->first())
        @if ($photoUrl = public_url_if_exists($firstPhoto->photo_path))
            <meta property="og:image" content="{{ url($photoUrl) }}">
        @endif
    @endif
@endpush

@section('content')
    <x-page-hero :title="$album->title" :subtitle="'Album foto — ' . $album->photos_count . ' foto'" />

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        @if ($album->photos->isEmpty())
            <x-empty-state message="Belum ada foto dalam album ini." />
        @else
            <div class="columns-1 gap-4 sm:columns-2 lg:columns-3 [&>figure]:mb-4">
                @foreach ($album->photos as $photo)
                    <figure class="group break-inside-avoid overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
                        @if ($photoUrl = public_url_if_exists($photo->photo_path))
                            <img
                                src="{{ $photoUrl }}"
                                alt="{{ $photo->caption ?? $album->title . ' - foto ' . $loop->iteration }}"
                                loading="lazy"
                                class="w-full object-cover transition duration-300 group-hover:scale-105"
                            >
                        @else
                            <div class="flex aspect-video w-full items-center justify-center bg-slate-100">
                                <svg class="h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                            </div>
                        @endif
                        @if ($photo->caption)
                            <figcaption class="px-4 py-3 text-sm text-slate-600">{{ $photo->caption }}</figcaption>
                        @endif
                    </figure>
                @endforeach
            </div>
        @endif

        <div class="mt-8">
            <a href="{{ route('galeri.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-600 hover:text-brand-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Galeri
            </a>
        </div>
    </section>
@endsection
