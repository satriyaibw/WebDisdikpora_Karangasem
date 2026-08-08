@extends('layouts.app')

@section('title', 'Kontak')

@section('metaDescription', 'Kontak Dinas Pendidikan, Kepemudaan dan Olahraga Kabupaten Karangasem, peta lokasi, dan kanal pengaduan SP4N-LAPOR.')

@section('content')
    <x-page-hero title="Hubungi Kami" subtitle="Kontak dan lokasi {{ settings('site.name', '') }}" />

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-[1fr_1.4fr]">
            <div class="space-y-5">
                <div class="rounded-2xl bg-white p-7 shadow-sm ring-1 ring-slate-200">
                    <h2 class="text-lg font-bold text-slate-900">Informasi Kontak</h2>
                    <ul class="mt-5 space-y-4 text-sm">
                        <li class="flex gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-brand-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                            <div>
                                <p class="font-semibold text-slate-900">Alamat</p>
                                <p class="mt-0.5 text-slate-600">{{ settings('site.address', '-') }}</p>
                            </div>
                        </li>
                        <li class="flex gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-brand-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                            </svg>
                            <div>
                                <p class="font-semibold text-slate-900">Email</p>
                                <p class="mt-0.5"><a href="mailto:{{ settings('site.email') }}" class="text-brand-600 hover:underline">{{ settings('site.email', '-') }}</a></p>
                            </div>
                        </li>
                        <li class="flex gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-brand-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                            </svg>
                            <div>
                                <p class="font-semibold text-slate-900">Telepon</p>
                                <p class="mt-0.5 text-slate-600">{{ settings('site.phone', '-') }}</p>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="rounded-2xl bg-gold-500 p-7 shadow-sm">
                    <h2 class="flex items-center gap-2 text-lg font-bold text-slate-900">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                        Pengaduan & Aspirasi
                    </h2>
                    <p class="mt-2 text-sm leading-6 text-slate-800">
                        Sampaikan pengaduan, aspirasi, atau permintaan informasi publik melalui kanal resmi pemerintah.
                    </p>
                    <a
                        href="https://www.lapor.go.id"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="mt-4 inline-flex items-center gap-2 rounded-full bg-slate-900 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-slate-700"
                    >
                        Laporkan via SP4N-LAPOR!
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                        </svg>
                    </a>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="text-base font-bold text-slate-900">Lokasi Kantor</h2>
                </div>
                @php
                    $mapAddress = urlencode(settings('site.address', 'Dinas Pendidikan, Kepemudaan dan Olahraga Kabupaten Karangasem'));
                @endphp
                <iframe
                    src="https://www.google.com/maps?q={{ $mapAddress }}&output=embed"
                    title="Peta lokasi {{ settings('site.name', '') }}"
                    allowfullscreen
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    class="h-[420px] w-full"
                ></iframe>
            </div>
        </div>
    </section>
@endsection
