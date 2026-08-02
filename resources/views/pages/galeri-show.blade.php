@extends('layouts.app')

@section('title', $album->title)

@section('metaDescription', Str::limit(strip_tags((string) $album->description), 160))

@section('content')
    <x-page-hero :title="$album->title" :subtitle="'Album foto — ' . $album->photos_count . ' foto'" />

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        @if ($album->photos->isEmpty())
            <x-empty-state message="Belum ada foto dalam album ini." />
        @else
            <div class="columns-1 gap-4 sm:columns-2 lg:columns-3 [&>figure]:mb-4">
                @foreach ($album->photos as $photo)
                    <figure class="group break-inside-avoid overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
                        <img
                            src="{{ Storage::url($photo->photo_path) }}"
                            alt="{{ $photo->caption ?? $album->title . ' - foto ' . $loop->iteration }}"
                            loading="lazy"
                            class="w-full object-cover transition duration-300 group-hover:scale-105"
                        >
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
