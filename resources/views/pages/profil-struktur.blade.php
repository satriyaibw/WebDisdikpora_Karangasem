@extends('layouts.app')

@section('title', 'Struktur Organisasi')

@section('content')
    <x-page-hero title="Struktur Organisasi" subtitle="Bagan struktur organisasi {{ settings('site.name', '') }}" />

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-10">
            <div class="mx-auto max-w-3xl">
                {{-- Puncak --}}
                <div class="flex justify-center">
                    <div class="rounded-lg border-t-4 border-gold-500 bg-slate-50 px-6 py-4 text-center shadow-sm">
                        <p class="text-sm font-bold text-slate-900">Kepala Dinas</p>
                        <p class="text-xs text-slate-500">Dinas Pendidikan, Kepemudaan dan Olahraga</p>
                    </div>
                </div>

                <div class="mx-auto h-8 w-px bg-slate-300"></div>

                {{-- Sekretariat --}}
                <div class="flex justify-center">
                    <div class="rounded-lg border-t-4 border-brand-500 bg-slate-50 px-6 py-4 text-center shadow-sm">
                        <p class="text-sm font-bold text-slate-900">Sekretariat</p>
                        <p class="text-xs text-slate-500">Sub Bagian Umum, Kepegawaian & Keuangan</p>
                    </div>
                </div>

                <div class="mx-auto h-8 w-px bg-slate-300"></div>

                {{-- 6 Bidang --}}
                <div class="grid gap-4 sm:grid-cols-3">
                    @foreach ($bidangs as $bidang)
                        <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 text-center shadow-sm">
                            <p class="text-xs font-bold leading-5 text-slate-800">{{ $bidang->name }}</p>
                            @if ($bidang->description)
                                <p class="mt-1 text-[11px] leading-4 text-slate-500">{{ $bidang->description }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>

                <p class="mt-8 text-center text-xs text-slate-400">
                    Bagan struktur ini disajikan secara ringkas. Detail tupoksi tersedia di kantor {{ settings('site.short_name', '') }}.
                </p>
            </div>
        </div>
    </section>
@endsection
