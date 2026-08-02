@extends('layouts.app')

@section('title', 'Struktur Organisasi')

@section('content')
    <x-page-hero title="Struktur Organisasi" subtitle="Bagan struktur organisasi {{ settings('site.name', '') }}" />

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-10">
            @php
                $strukturImage = public_url_if_exists(settings('profile.struktur_image'));
            @endphp

            @if ($strukturImage)
                <figure class="mx-auto max-w-5xl">
                    <img
                        src="{{ $strukturImage }}"
                        alt="Bagan struktur organisasi {{ settings('site.name', '') }}"
                        loading="lazy"
                        class="h-auto w-full"
                    >
                </figure>
            @else
                <div class="mx-auto max-w-3xl py-10 text-center">
                    <p class="text-sm text-slate-500">
                        Bagan struktur organisasi belum tersedia.
                    </p>
                    <p class="mt-2 text-sm text-slate-500">
                        Silakan hubungi <a href="mailto:{{ settings('site.email') }}" class="font-medium text-brand-600 hover:text-brand-700">{{ settings('site.email') }}</a>
                        @if (settings('site.phone'))
                            atau <a href="tel:{{ settings('site.phone') }}" class="font-medium text-brand-600 hover:text-brand-700">{{ settings('site.phone') }}</a>
                        @endif
                        untuk informasi lebih lanjut.
                    </p>
                </div>
            @endif

            <p class="mt-8 text-center text-xs text-slate-400">
                Bagan struktur ini disajikan sebagaimana diterbitkan oleh {{ settings('site.short_name', '') }}.
            </p>
        </div>
    </section>
@endsection
