@extends('layouts.app')

@section('title', 'Pengumuman')

@section('metaDescription', 'Pengumuman resmi Disdikpora Karangasem beserta lampiran.')

@section('content')
    <x-page-hero title="Pengumuman" subtitle="Pengumuman resmi dari {{ settings('site.name', '') }}" />

    <section class="mx-auto max-w-4xl px-4 py-10 sm:px-6">
        @if ($announcements->isEmpty())
            <x-empty-state message="Belum ada pengumuman." />
        @else
            <div class="space-y-5">
                @foreach ($announcements as $announcement)
                    <article class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                        <div class="flex flex-wrap items-center gap-3">
                            @if ($announcement->is_important)
                                <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-600 ring-1 ring-red-200">Penting</span>
                            @endif
                            @if ($announcement->announcement_number)
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $announcement->announcement_number }}</span>
                            @endif
                            <time datetime="{{ $announcement->announcement_date?->toDateString() }}" class="text-xs text-slate-500">
                                {{ $announcement->announcement_date?->translatedFormat('d F Y') }}
                            </time>
                        </div>
                        <h2 class="mt-3 text-lg font-bold text-slate-900">{{ $announcement->title }}</h2>
                        <div class="prose prose-sm mt-2 max-w-none text-slate-600">
                            {!! Purify::clean($announcement->content) !!}
                        </div>
                        @if ($announcement->attachment_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($announcement->attachment_path))
                            <a
                                href="{{ Storage::url($announcement->attachment_path) }}"
                                class="mt-4 inline-flex items-center gap-2 rounded-lg bg-brand-50 px-4 py-2 text-sm font-semibold text-brand-700 ring-1 ring-brand-200 transition hover:bg-brand-100"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                </svg>
                                Unduh Lampiran
                            </a>
                        @endif
                    </article>
                @endforeach
            </div>
            <div class="mt-10">{{ $announcements->links() }}</div>
        @endif
    </section>
@endsection
