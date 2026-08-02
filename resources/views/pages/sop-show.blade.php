@extends('layouts.app')

@section('title', $sopDocument->title)

@section('metaDescription', Str::limit(strip_tags((string) $sopDocument->description), 160))

@section('content')
    <x-page-hero :title="$sopDocument->title" :subtitle="'Bidang: ' . ($sopDocument->bidang->name ?? '-')" />

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-[2fr_1fr]">
            <div>
                @if ($sopDocument->fileExists)
                    <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                        <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-3">
                            <p class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                                <svg class="h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                Pratinjau Dokumen
                            </p>
                            <a
                                href="{{ $sopDocument->fileUrl }}"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-gold-500 px-4 py-1.5 text-xs font-bold text-slate-900 transition hover:bg-gold-400"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                </svg>
                                Unduh Berkas
                            </a>
                        </div>
                        <iframe
                            src="{{ $sopDocument->fileUrl }}#toolbar=1&navpanes=1"
                            title="Pratinjau {{ $sopDocument->title }}"
                            class="h-[70vh] w-full"
                        ></iframe>
                    </div>
                @else
                    <div class="rounded-2xl bg-white p-10 text-center shadow-sm ring-1 ring-slate-200">
                        <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                        <h2 class="mt-4 text-base font-bold text-slate-900">Berkas tidak tersedia</h2>
                        <p class="mt-2 text-sm text-slate-500">
                            Maaf, berkas SOP ini sedang tidak tersedia. Silakan hubungi kami melalui halaman kontak atau kunjungi kembali nanti.
                        </p>
                        <a href="{{ route('kontak') }}" class="mt-4 inline-flex rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-600">Hubungi Kami</a>
                    </div>
                @endif
            </div>

            <aside>
                <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                    <div class="bg-brand-500 px-6 py-4">
                        <h2 class="text-base font-bold text-white">Detail Dokumen</h2>
                    </div>
                    <dl class="divide-y divide-slate-100 text-sm">
                        <div class="flex items-start justify-between gap-4 px-6 py-4">
                            <dt class="text-slate-500">Nomor SOP</dt>
                            <dd class="text-right font-semibold text-slate-900">{{ $sopDocument->sop_number ?? '-' }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4 px-6 py-4">
                            <dt class="text-slate-500">Tanggal Pengesahan</dt>
                            <dd class="text-right font-semibold text-slate-900">{{ $sopDocument->issuance_date?->translatedFormat('d F Y') ?? '-' }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4 px-6 py-4">
                            <dt class="text-slate-500">Bidang</dt>
                            <dd class="text-right font-semibold text-slate-900">{{ $sopDocument->bidang->name ?? '-' }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4 px-6 py-4">
                            <dt class="text-slate-500">Ukuran Berkas</dt>
                            <dd class="text-right font-semibold text-slate-900">{{ \App\Models\SopDocument::formatFileSize($sopDocument->file_size) }}</dd>
                        </div>
                    </dl>
                    @if ($sopDocument->description)
                        <div class="border-t border-slate-100 px-6 py-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Deskripsi</p>
                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ strip_tags($sopDocument->description) }}</p>
                        </div>
                    @endif
                </div>

                <a href="{{ route('sop.index') }}" class="mt-5 inline-flex items-center gap-1 text-sm font-semibold text-brand-600 hover:text-brand-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Kembali ke Daftar SOP
                </a>
            </aside>
        </div>
    </section>
@endsection
