@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
    {{-- Hero slider --}}
    @if ($sliders->isNotEmpty())
        <section class="relative" x-data="{ current: 0 }" aria-label="Sorotan utama" x-cloak>
            <div class="relative h-[420px] w-full overflow-hidden sm:h-[480px]">
                @foreach ($sliders as $index => $slider)
                    @php($sliderUrl = public_url_if_exists($slider->image))
                    <div
                        class="absolute inset-0 transition-opacity duration-500 {{ $index === 0 ? '' : 'opacity-0' }}"
                        :class="current === {{ $index }} ? 'opacity-100' : 'opacity-0'"
                        x-cloak
                    >
                        @if ($sliderUrl)
                            <img
                                src="{{ $sliderUrl }}"
                                alt="{{ $slider->title ?? 'Slide ' . ($index + 1) }}"
                                @if ($index > 0) loading="lazy" @endif
                                class="h-full w-full object-cover"
                            >
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/30 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 mx-auto max-w-7xl px-4 pb-10 sm:px-6 lg:px-8">
                            @if ($slider->title)
                                <h1 class="max-w-3xl text-2xl font-bold text-white sm:text-4xl">{{ $slider->title }}</h1>
                            @endif
                            @if ($slider->description)
                                <p class="mt-2 max-w-2xl text-sm text-slate-100 sm:text-base">{{ $slider->description }}</p>
                            @endif
                            @if ($slider->link)
                                <a href="{{ $slider->link }}" target="_blank" rel="noopener noreferrer"
                                   class="mt-4 inline-flex items-center gap-2 rounded-full bg-gold-500 px-5 py-2 text-sm font-bold text-slate-900 transition hover:bg-gold-400">
                                    Selengkapnya
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($sliders->count() > 1)
                <button type="button" class="absolute left-3 top-1/2 -translate-y-1/2 rounded-full bg-white/20 p-2 text-white backdrop-blur transition hover:bg-white/40"
                        x-on:click="current = (current - 1 + {{ $sliders->count() }}) % {{ $sliders->count() }}" aria-label="Slide sebelumnya">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                </button>
                <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full bg-white/20 p-2 text-white backdrop-blur transition hover:bg-white/40"
                        x-on:click="current = (current + 1) % {{ $sliders->count() }}" aria-label="Slide berikutnya">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </button>
                <div class="absolute bottom-4 left-1/2 flex -translate-x-1/2 gap-2">
                    @foreach ($sliders as $index => $slider)
                        <button type="button" class="h-2 w-2 rounded-full transition {{ $index === 0 ? 'bg-gold-500' : 'bg-white/60' }}"
                                :class="current === {{ $index }} ? 'bg-gold-500' : 'bg-white/60'"
                                x-on:click="current = {{ $index }}" aria-label="Ke slide {{ $index + 1 }}"></button>
                    @endforeach
                </div>
            @endif
        </section>
    @endif

    {{-- Running text pengumuman --}}
    @if ($runningTexts->isNotEmpty())
        <div class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-7xl items-center gap-3 px-4 py-2.5 sm:px-6 lg:px-8">
                <span class="shrink-0 rounded-full bg-gold-500 px-3 py-0.5 text-xs font-bold text-slate-900">PENGUMUMAN</span>
                <div class="relative flex-1 overflow-hidden">
                    <div class="flex animate-marquee gap-10 whitespace-nowrap" x-data x-init="$el.scrollWidth > $el.parentElement.clientWidth && $el.setAttribute('style', 'animation-duration: ' + ($el.scrollWidth / 40) + 's')">
                        @foreach ($runningTexts as $text)
                            <span class="text-sm text-slate-700">{{ $text->title }}</span>
                        @endforeach
                    </div>
                </div>
                <a href="{{ route('pengumuman.index') }}" class="hidden shrink-0 text-xs font-semibold text-brand-600 hover:text-brand-700 sm:block">Semua</a>
            </div>
        </div>
    @endif

    {{-- Pintasan utama --}}
    <section class="relative z-10 mx-auto -mt-0 max-w-7xl px-4 pt-10 sm:px-6 lg:px-8">
        <div class="grid gap-5 sm:grid-cols-3">
            <a href="{{ route('layanan.index') }}" class="group rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                </span>
                <h3 class="mt-4 text-lg font-bold text-slate-900 group-hover:text-brand-600">Layanan Publik</h3>
                <p class="mt-1 text-sm text-slate-600">Katalog layanan, persyaratan, alur, dan estimasi waktu pelayanan.</p>
            </a>
            <a href="{{ route('sop.index') }}" class="group rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-lg bg-gold-50 text-gold-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                </span>
                <h3 class="mt-4 text-lg font-bold text-slate-900 group-hover:text-gold-600">Dokumen SOP</h3>
                <p class="mt-1 text-sm text-slate-600">Standar operasional prosedur pelayanan dengan pratinjau PDF.</p>
            </a>
            <a href="{{ route('ppid.index') }}" class="group rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:shadow-md">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                </span>
                <h3 class="mt-4 text-lg font-bold text-slate-900 group-hover:text-brand-600">Informasi PPID</h3>
                <p class="mt-1 text-sm text-slate-600">Dokumen informasi publik berkala, serta merta, dan setiap saat.</p>
            </a>
        </div>
    </section>

    {{-- Berita terbaru --}}
    <section class="mx-auto max-w-7xl px-4 pt-14 sm:px-6 lg:px-8">
        <x-section-heading title="Berita Terbaru" subtitle="Informasi terkini dari Disdikpora Karangasem" :link="route('berita.index')" />
        @if ($latestNews->isEmpty())
            <x-empty-state message="Belum ada berita. Silakan kunjungi kembali nanti." />
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($latestNews as $news)
                    <x-news-card :news="$news" />
                @endforeach
            </div>
        @endif
    </section>

    {{-- Agenda dinas --}}
    @if ($upcomingAgendas->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pt-14 sm:px-6 lg:px-8">
            <x-section-heading title="Agenda Dinas" subtitle="Kegiatan terdekat" :link="route('agenda.index')" />
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($upcomingAgendas as $agenda)
                    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                        <div class="flex items-center gap-3">
                            <div class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-lg bg-brand-500 text-white">
                                <span class="text-lg font-bold leading-none">{{ $agenda->date->format('d') }}</span>
                                <span class="text-[10px] uppercase">{{ $agenda->date->translatedFormat('M') }}</span>
                            </div>
                            <div class="min-w-0">
                                <h3 class="line-clamp-2 text-sm font-bold text-slate-900">{{ $agenda->title }}</h3>
                                <p class="mt-0.5 text-xs text-slate-500">{{ $agenda->location ?? 'Lokasi menyusul' }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Infografis & video --}}
    @if ($infographics->isNotEmpty() || $videos->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pt-14 sm:px-6 lg:px-8">
            @if ($infographics->isNotEmpty())
                <x-section-heading title="Infografis" subtitle="Data dan informasi dalam format visual" :link="route('galeri.index')" />
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($infographics as $infographic)
                        @php($infographicUrl = public_url_if_exists($infographic->image))
                        <a href="{{ $infographic->link ?: route('galeri.index') }}" target="{{ $infographic->link ? '_blank' : '_self' }}" rel="noopener noreferrer" class="group overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
                            @if ($infographicUrl)
                                <img src="{{ $infographicUrl }}" alt="{{ $infographic->title }}" loading="lazy" class="aspect-[4/3] w-full object-cover transition duration-300 group-hover:scale-105">
                            @endif
                            <p class="px-4 py-3 text-sm font-semibold text-slate-800 group-hover:text-brand-600">{{ $infographic->title }}</p>
                        </a>
                    @endforeach
                </div>
            @endif

            @if ($videos->isNotEmpty())
                <div class="mt-12">
                    <x-section-heading title="Video Galeri" subtitle="Video kegiatan dinas" :link="route('galeri.index')" />
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
    @endif
@endsection
