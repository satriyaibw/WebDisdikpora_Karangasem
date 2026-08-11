@props(['title' => null, 'subtitle' => null])

<section class="bg-brand-500">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <nav class="text-xs text-white" aria-label="Breadcrumb">
            <ol class="flex items-center gap-1.5">
                <li><a href="{{ route('home') }}" class="font-semibold underline-offset-2 hover:text-gold-300 hover:underline">Beranda</a></li>
                <li class="text-white/60">/</li>
                <li aria-current="page" class="font-semibold text-white">{{ $title }}</li>
            </ol>
        </nav>
        <h1 class="mt-2 text-3xl font-bold text-white sm:text-4xl">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-2 max-w-3xl text-sm text-white">{{ $subtitle }}</p>
        @endif
    </div>
</section>
