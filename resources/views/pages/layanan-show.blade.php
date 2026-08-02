@extends('layouts.app')

@section('title', $service->name)

@section('metaDescription', Str::limit(strip_tags((string) $service->short_description), 160))

@section('content')
    <x-page-hero :title="$service->name" :subtitle="'Bidang: ' . ($service->bidang->name ?? '-')" />

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-[2fr_1fr]">
            <div class="space-y-8">
                <article class="rounded-2xl bg-white p-7 shadow-sm ring-1 ring-slate-200">
                    <h2 class="text-lg font-bold text-slate-900">Deskripsi Layanan</h2>
                    <div class="prose prose-sm mt-4 max-w-none text-slate-700">
                        {!! Purify::clean($service->description) !!}
                    </div>
                </article>

                @if ($service->requirements)
                    <article class="rounded-2xl bg-white p-7 shadow-sm ring-1 ring-slate-200">
                        <h2 class="text-lg font-bold text-slate-900">Persyaratan</h2>
                        <div class="prose prose-sm mt-4 max-w-none text-slate-700">
                            {!! Purify::clean($service->requirements) !!}
                        </div>
                    </article>
                @endif

                @if ($service->procedure)
                    <article class="rounded-2xl bg-white p-7 shadow-sm ring-1 ring-slate-200">
                        <h2 class="text-lg font-bold text-slate-900">Bagan Alur Prosedur</h2>
                        <div class="prose prose-sm mt-4 max-w-none text-slate-700">
                            {!! Purify::clean($service->procedure) !!}
                        </div>
                    </article>
                @endif
            </div>

            <aside class="space-y-5">
                <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                    <div class="bg-brand-500 px-6 py-4">
                        <h2 class="text-base font-bold text-white">Informasi Layanan</h2>
                    </div>
                    <dl class="divide-y divide-slate-100 text-sm">
                        <div class="flex items-start justify-between gap-4 px-6 py-4">
                            <dt class="text-slate-500">Bidang</dt>
                            <dd class="text-right font-semibold text-slate-900">{{ $service->bidang->name ?? '-' }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4 px-6 py-4">
                            <dt class="text-slate-500">Waktu (SLA)</dt>
                            <dd class="text-right font-semibold text-slate-900">{{ $service->estimated_time ?? '-' }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4 px-6 py-4">
                            <dt class="text-slate-500">Biaya</dt>
                            <dd class="text-right font-semibold text-slate-900">
                                {{ preg_match('/[1-9]/', (string) $service->cost) ? $service->cost : 'Gratis' }}
                            </dd>
                        </div>
                        @if ($service->pic_name)
                            <div class="px-6 py-4">
                                <dt class="text-slate-500">Penanggung Jawab</dt>
                                <dd class="mt-1 font-semibold text-slate-900">{{ $service->pic_name }}</dd>
                                @if ($service->pic_contact)
                                    <dd class="mt-0.5 text-xs text-slate-500">{{ $service->pic_contact }}</dd>
                                @endif
                            </div>
                        @endif
                    </dl>
                    @if ($service->hasFormTemplate)
                        <div class="border-t border-slate-100 px-6 py-4">
                            <a
                                href="{{ $service->form_template_url }}"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-gold-500 px-4 py-2.5 text-sm font-bold text-slate-900 transition hover:bg-gold-400"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                </svg>
                                Unduh Template Formulir
                            </a>
                            <p class="mt-2 text-center text-xs text-slate-400">{{ \App\Models\Service::formatFileSize($service->file_size) }}</p>
                        </div>
                    @endif
                </div>

                <a href="{{ route('layanan.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-600 hover:text-brand-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Kembali ke Katalog Layanan
                </a>
            </aside>
        </div>
    </section>
@endsection
