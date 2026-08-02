@extends('layouts.app')

@section('title', 'Agenda')

@section('metaDescription', 'Agenda kegiatan Dinas Pendidikan, Kepemudaan dan Olahraga Kabupaten Karangasem.')

@section('content')
    <x-page-hero title="Agenda Dinas" subtitle="Agenda kegiatan {{ settings('site.short_name', '') }}" />

    <section class="mx-auto max-w-4xl px-4 py-10 sm:px-6">
        <x-section-heading title="Agenda Mendatang" />

        @if ($upcoming->isEmpty())
            <x-empty-state message="Tidak ada agenda yang akan datang." />
        @else
            <div class="space-y-4">
                @foreach ($upcoming as $agenda)
                    <article class="flex gap-5 rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                        <div class="flex h-16 w-16 shrink-0 flex-col items-center justify-center rounded-xl bg-brand-500 text-white">
                            <span class="text-2xl font-bold leading-none">{{ $agenda->date->format('d') }}</span>
                            <span class="text-[10px] font-semibold uppercase">{{ $agenda->date->translatedFormat('M Y') }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-base font-bold text-slate-900">{{ $agenda->title }}</h2>
                                <span class="rounded-full {{ $agenda->statusLabel() === 'Hari Ini' ? 'bg-red-50 text-red-600 ring-red-200' : 'bg-gold-50 text-gold-700 ring-gold-200' }} px-2.5 py-0.5 text-xs font-bold ring-1">
                                    {{ $agenda->statusLabel() }}
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-slate-600">
                                {{ $agenda->date->translatedFormat('l, d F Y') }}
                                @if ($agenda->start_time) · {{ \Carbon\Carbon::parse($agenda->start_time)->format('H:i') }} WITA @endif
                            </p>
                            @if ($agenda->location || $agenda->pic)
                                <p class="mt-0.5 text-xs text-slate-500">
                                    {{ $agenda->location ? 'Lokasi: ' . $agenda->location : '' }}
                                    {{ $agenda->pic ? '· PIC: ' . $agenda->pic : '' }}
                                </p>
                            @endif
                            @if ($agenda->description)
                                <p class="mt-2 text-sm leading-6 text-slate-600">{{ $agenda->description }}</p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        @if ($finished->isNotEmpty())
            <div class="mt-14">
                <x-section-heading title="Agenda Terlaksana" />
                <div class="space-y-3">
                    @foreach ($finished as $agenda)
                        <article class="flex gap-4 rounded-xl bg-white p-4 opacity-75 shadow-sm ring-1 ring-slate-200">
                            <div class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-lg bg-slate-200 text-slate-600">
                                <span class="text-lg font-bold leading-none">{{ $agenda->date->format('d') }}</span>
                                <span class="text-[10px] font-semibold uppercase">{{ $agenda->date->translatedFormat('M') }}</span>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-bold text-slate-700">{{ $agenda->title }}</h3>
                                <p class="mt-0.5 text-xs text-slate-500">{{ $agenda->date->translatedFormat('l, d F Y') }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif
    </section>
@endsection
