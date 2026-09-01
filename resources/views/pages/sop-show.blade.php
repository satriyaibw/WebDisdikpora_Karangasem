@extends('layouts.app')

@section('title', $sopDocument->title)

@section('metaDescription', Str::limit(strip_tags((string) $sopDocument->description), 160))

@section('content')
    <x-page-hero :title="$sopDocument->title" :subtitle="'Bidang: ' . ($sopDocument->bidang->name ?? '-')" />

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-[2fr_1fr]">
            <div>
                @if ($sopDocument->fileExists)
                    <x-pdf-viewer :url="route('sop.preview', $sopDocument, false)" :title="'Pratinjau ' . $sopDocument->title" :downloadUrl="route('sop.download', $sopDocument)" />
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
