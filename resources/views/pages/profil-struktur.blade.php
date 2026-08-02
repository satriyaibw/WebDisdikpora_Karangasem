@extends('layouts.app')

@section('title', 'Struktur Organisasi')

@section('content')
    <x-page-hero title="Struktur Organisasi" subtitle="Bagan struktur organisasi {{ settings('site.name', '') }}" />

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-10">
            <div class="mx-auto max-w-3xl">
                @forelse ($tree as $node)
                    @php
                        $children = $node->children;
                        $descendants = $children->flatMap(fn ($child) => $child->children);
                    @endphp

                    {{-- Puncak --}}
                    <div class="flex justify-center">
                        <div class="rounded-lg border-t-4 border-gold-500 bg-slate-50 px-6 py-4 text-center shadow-sm">
                            <p class="text-sm font-bold text-slate-900">{{ $node->jabatan }}</p>
                            @if ($node->nama && $node->nama !== '-')
                                <p class="text-xs text-slate-500">{{ $node->nama }}</p>
                            @endif
                        </div>
                    </div>

                    @foreach ($children as $child)
                        @php $grandchildren = $child->children; @endphp

                        <div class="mx-auto h-8 w-px bg-slate-300"></div>

                        {{-- Sekretariat / level 2 --}}
                        <div class="flex justify-center">
                            <div class="rounded-lg border-t-4 border-brand-500 bg-slate-50 px-6 py-4 text-center shadow-sm">
                                <p class="text-sm font-bold text-slate-900">{{ $child->jabatan }}</p>
                                @if ($child->nama && $child->nama !== '-')
                                    <p class="text-xs text-slate-500">{{ $child->nama }}</p>
                                @endif
                            </div>
                        </div>

                        @if ($grandchildren->isNotEmpty())
                            <div class="mx-auto h-8 w-px bg-slate-300"></div>

                            {{-- Bidang / level 3 --}}
                            <div class="grid gap-4 sm:grid-cols-3">
                                @foreach ($grandchildren as $grandchild)
                                    <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 text-center shadow-sm">
                                        <p class="text-xs font-bold leading-5 text-slate-800">{{ $grandchild->jabatan }}</p>
                                        @if ($grandchild->nama && $grandchild->nama !== '-')
                                            <p class="mt-1 text-[11px] leading-4 text-slate-500">{{ $grandchild->nama }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endforeach

                    {{-- Level 4+ (jika ada) --}}
                    @if ($descendants->isNotEmpty() && $descendants->contains(fn ($d) => $d->children->isNotEmpty()))
                        <div class="mt-6 rounded-lg border border-dashed border-slate-300 p-4 text-center text-xs text-slate-500">
                            Struktur lengkap dapat dilihat di kantor {{ settings('site.short_name', '') }}.
                        </div>
                    @endif
                @empty
                    <p class="py-10 text-center text-sm text-slate-500">
                        Bagan struktur organisasi belum tersedia.
                    </p>
                @endforelse

                <p class="mt-8 text-center text-xs text-slate-400">
                    Bagan struktur ini disajikan secara ringkas. Detail tupoksi tersedia di kantor {{ settings('site.short_name', '') }}.
                </p>
            </div>
        </div>
    </section>
@endsection
