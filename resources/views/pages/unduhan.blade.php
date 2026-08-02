@extends('layouts.app')

@section('title', 'Pusat Unduhan')

@section('metaDescription', 'Pusat unduhan formulir dan juknis dari Disdikpora Karangasem.')

@section('content')
    <x-page-hero title="Pusat Unduhan" subtitle="Formulir, petunjuk teknis, dan berkas lainnya" />

    <section class="mx-auto max-w-5xl px-4 py-10 sm:px-6">
        @if ($groups->isEmpty())
            <x-empty-state message="Belum ada berkas unduhan." />
        @else
            @foreach ($groups as $type => $files)
                <div class="mb-12">
                    <x-section-heading :title="$typeLabels[$type] ?? ucfirst($type)" :subtitle="$files->count() . ' berkas'" />

                    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 text-sm">
                                <thead class="bg-slate-50">
                                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        <th class="px-5 py-3">Nama Berkas</th>
                                        <th class="px-5 py-3">Deskripsi</th>
                                        <th class="px-5 py-3">Ukuran</th>
                                        <th class="px-5 py-3 text-right">Unduh</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($files as $file)
                                        <tr class="hover:bg-brand-50/50">
                                            <td class="px-5 py-4 font-semibold text-slate-900">
                                                <div class="flex items-center gap-2.5">
                                                    <svg class="h-5 w-5 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                                    </svg>
                                                    <span>{{ $file->title }}</span>
                                                </div>
                                            </td>
                                            <td class="max-w-xs px-5 py-4 text-slate-600">{{ Str::limit(strip_tags((string) $file->description), 90) }}</td>
                                            <td class="px-5 py-4 text-slate-600">{{ \App\Models\DownloadFile::formatFileSize($file->file_size) }}</td>
                                            <td class="px-5 py-4 text-right">
                                                <a
                                                    href="{{ Storage::url($file->file_path) }}"
                                                    class="inline-flex items-center gap-1.5 rounded-lg bg-gold-500 px-3 py-1.5 text-xs font-bold text-slate-900 transition hover:bg-gold-400"
                                                >
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                                    </svg>
                                                    Unduh
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </section>
@endsection
