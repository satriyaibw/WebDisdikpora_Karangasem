<div>
    <div class="mb-6 grid gap-4 lg:grid-cols-[1fr_auto]">
        <div>
            <label for="service-search" class="sr-only">Cari layanan</label>
            <input
                id="service-search"
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari nama layanan..."
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

    @if ($services->isEmpty())
        <x-empty-state message="Tidak ada layanan yang cocok dengan pencarian." />
    @else
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($services as $service)
                <article class="group flex h-full flex-col rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="text-lg font-bold text-slate-900 group-hover:text-brand-600">
                            <a href="{{ route('layanan.show', $service->slug) }}">{{ $service->name }}</a>
                        </h3>
                    </div>
                    @if ($service->bidang)
                        <span class="mt-1.5 w-fit rounded-full bg-gold-50 px-2.5 py-0.5 text-xs font-semibold text-gold-700 ring-1 ring-gold-200">{{ $service->bidang->name }}</span>
                    @endif
                    <p class="mt-3 line-clamp-3 flex-1 text-sm text-slate-600">{{ Str::limit(strip_tags((string) $service->short_description), 120) }}</p>
                    <a href="{{ route('layanan.show', $service->slug) }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-brand-600 hover:text-brand-700">
                        Lihat Detail
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                </article>
            @endforeach
        </div>

        <div class="mt-8">{{ $services->links() }}</div>
    @endif
</div>
