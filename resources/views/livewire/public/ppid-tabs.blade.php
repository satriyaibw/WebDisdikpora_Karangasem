<div>
    @if ($categories->isEmpty())
        <x-empty-state message="Belum ada kategori dokumen PPID. Silakan kunjungi kembali nanti." />
    @else
    <div class="mb-6 flex flex-wrap gap-2" role="tablist" aria-label="Kategori dokumen PPID">
        @foreach ($categories as $category)
            <button
                type="button"
                role="tab"
                wire:click="setCategory({{ json_encode($category->slug) }})"
                aria-selected="{{ $activeCategorySlug === $category->slug ? 'true' : 'false' }}"
                class="rounded-t-lg px-5 py-2.5 text-sm font-semibold transition {{ $activeCategorySlug === $category->slug ? 'bg-white text-brand-700 shadow ring-1 ring-slate-200' : 'bg-slate-200 text-slate-600 hover:bg-slate-300' }}"
            >
                {{ $category->name }}
                <span class="ml-1 rounded-full bg-gold-500 px-2 py-0.5 text-xs font-bold text-white">{{ $category->documents_count }}</span>
            </button>
        @endforeach
    </div>

    <div class="rounded-b-xl rounded-tr-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <div class="mb-4 max-w-sm">
            <label for="ppid-search" class="sr-only">Cari dokumen PPID</label>
            <input
                id="ppid-search"
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari judul / nomor dokumen..."
                class="w-full rounded-lg border-slate-300 py-2 pl-9 pr-4 text-sm focus:border-brand-500 focus:ring-brand-500"
            >
        </div>

        @if (! $documents)
            <x-empty-state message="Kategori tidak ditemukan." />
        @elseif ($documents->documents->isEmpty())
            <x-empty-state message="Belum ada dokumen pada kategori ini." />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-4 py-3">Judul Dokumen</th>
                            <th class="px-4 py-3">Nomor</th>
                            <th class="px-4 py-3">Tahun</th>
                            <th class="px-4 py-3">Ukuran</th>
                            <th class="px-4 py-3 text-right">Pratinjau</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($documents->documents as $document)
                            <tr class="hover:bg-brand-50/50">
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $document->title }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $document->doc_number ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $document->year ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ \App\Models\PpidDocument::formatFileSize($document->file_size) }}</td>
                                <td class="px-4 py-3 text-right">
                                    @if ($fileUrl = public_url_if_exists($document->file_path))
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a
                                                href="{{ $fileUrl }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex items-center gap-1 rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-brand-600"
                                            >
                                                Buka PDF
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                                </svg>
                                            </a>
                                            <a
                                                href="{{ route('ppid.download', $document) }}"
                                                class="inline-flex items-center gap-1 rounded-lg bg-gold-500 px-3 py-1.5 text-xs font-bold text-slate-900 transition hover:bg-gold-400"
                                            >
                                                Unduh
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                                </svg>
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400">Tidak tersedia</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    @endif
</div>
