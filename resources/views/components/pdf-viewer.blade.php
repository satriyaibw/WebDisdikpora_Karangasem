@props(['url', 'title' => 'Pratinjau Dokumen', 'downloadUrl' => null])

<div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
    <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-3">
        <p class="flex items-center gap-2 text-sm font-semibold text-slate-700">
            <svg class="h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
            Pratinjau Dokumen
        </p>
        <div class="flex items-center gap-2">
            <a href="{{ $url }}" target="_blank" rel="noopener" class="hidden text-xs font-semibold text-brand-600 hover:text-brand-700 md:inline-flex">Buka di Tab Baru</a>
            @if($downloadUrl)
                <a href="{{ $downloadUrl }}" class="inline-flex items-center gap-1.5 rounded-lg bg-gold-500 px-4 py-1.5 text-xs font-bold text-slate-900 transition hover:bg-gold-400">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Unduh Berkas
                </a>
            @endif
        </div>
    </div>

    {{-- Desktop: native iframe (PDFium) --}}
    <iframe
        src="{{ $url }}#toolbar=1&navpanes=1"
        title="{{ $title }}"
        class="hidden h-[70vh] w-full md:block"
        loading="lazy"
    ></iframe>

    {{-- Mobile: lightweight hint (Brave/Chrome Android has no iframe PDF viewer) --}}
    <div class="md:hidden p-6 text-center">
        <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
        </svg>
        <p class="mt-3 text-sm font-semibold text-slate-700">Pratinjau tidak tersedia di pratinjau mobile</p>
        <p class="mt-1 text-xs text-slate-500">Silakan buka di tab baru untuk pratinjau sistem atau unduh berkas. Lebih ringan dan cepat.</p>
        <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:justify-center">
            <a href="{{ $url }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-600">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                </svg>
                Buka di Tab Baru
            </a>
            @if($downloadUrl)
                <a href="{{ $downloadUrl }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-gold-500 px-4 py-2 text-sm font-bold text-slate-900 transition hover:bg-gold-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Unduh Berkas
                </a>
            @endif
        </div>
    </div>
</div>
