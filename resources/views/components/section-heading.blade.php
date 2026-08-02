@props(['title' => null, 'subtitle' => null, 'link' => null, 'linkLabel' => 'Lihat Semua'])

<div class="mb-6 flex flex-wrap items-end justify-between gap-3">
    <div>
        <h2 class="text-2xl font-bold text-slate-900 sm:text-3xl">
            <span class="mr-2 inline-block h-6 w-1.5 rounded-full bg-gold-500 align-middle"></span>{{ $title }}
        </h2>
        @if ($subtitle)
            <p class="mt-1 text-sm text-slate-500">{{ $subtitle }}</p>
        @endif
    </div>
    @if ($link)
        <a href="{{ $link }}" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-600 hover:text-brand-700">
            {{ $linkLabel }}
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
            </svg>
        </a>
    @endif
</div>
