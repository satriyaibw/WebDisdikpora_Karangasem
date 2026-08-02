<div>
    <div class="mb-6 grid gap-4 lg:grid-cols-[1fr_auto]">
        <div>
            <label for="sop-search" class="sr-only">Cari SOP</label>
            <input
                id="sop-search"
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari judul / nomor SOP..."
                class="w-full rounded-lg border-slate-300 py-2.5 pl-4 pr-10 text-sm focus:border-brand-500 focus:ring-brand-500"
            >
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button
                type="button"
                wire:click="setBidang(null)"
                class="rounded-full px-4 py-1.5 text-sm font-semibold transition {{ $bidangId === null ? 'bg-brand-500 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-300 hover:bg-brand-50' }}"
            >
                Semua Bidang
            </button>
            @foreach ($bidangs as $bidang)
                <button
                    type="button"
                    wire:click="setBidang('{{ $bidang->id }}')"
                    wire:key="bidang-{{ $bidang->id }}"
                    class="rounded-full px-4 py-1.5 text-sm font-semibold transition {{ $bidangId == $bidang->id ? 'bg-brand-500 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-300 hover:bg-brand-50' }}"
                >
                    {{ $bidang->name }}
                </button>
            @endforeach
        </div>
    </div>

    @if ($sops->isEmpty())
        <x-empty-state message="Tidak ada dokumen SOP yang cocok dengan pencarian." />
    @else
        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-5 py-3">Judul SOP</th>
                            <th class="px-5 py-3">Nomor</th>
                            <th class="px-5 py-3">Bidang</th>
                            <th class="px-5 py-3">Ukuran</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($sops as $sop)
                            <tr class="hover:bg-brand-50/50">
                                <td class="px-5 py-4 font-semibold text-slate-900">
                                    <a href="{{ route('sop.show', $sop->slug) }}" class="hover:text-brand-600">{{ $sop->title }}</a>
                                </td>
                                <td class="px-5 py-4 text-slate-600">{{ $sop->sop_number ?? '-' }}</td>
                                <td class="px-5 py-4">
                                    @if ($sop->bidang)
                                        <span class="rounded-full bg-gold-50 px-2.5 py-0.5 text-xs font-semibold text-gold-700 ring-1 ring-gold-200">{{ $sop->bidang->name }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-slate-600">{{ \App\Models\SopDocument::formatFileSize($sop->file_size) }}</td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('sop.show', $sop->slug) }}" class="inline-flex items-center gap-1 rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-brand-600">
                                        Pratinjau
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-8">{{ $sops->links() }}</div>
    @endif
</div>
