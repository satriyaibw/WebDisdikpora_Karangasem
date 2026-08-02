@extends('layouts.app')

@section('title', 'Profil Instansi')

@section('content')
    <x-page-hero title="Profil Instansi" subtitle="Profil {{ settings('site.name', '') }}" />

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-[2fr_1fr]">
            <div class="space-y-10">
                <article class="rounded-2xl bg-white p-7 shadow-sm ring-1 ring-slate-200">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 class="text-xl font-bold text-slate-900">Sambutan Kepala Dinas</h2>
                        <span class="rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold text-brand-700">Sekretariat Dinas</span>
                    </div>
                    <p class="mt-5 text-sm leading-7 text-slate-700">
                        Assalamualaikum warahmatullahi wabarakatuh, salam sejahtera bagi kita semua.
                    </p>
                    <p class="mt-3 text-sm leading-7 text-slate-700">
                        Selamat datang di portal resmi {{ settings('site.name', '') }}. Portal ini kami hadirkan sebagai
                        wujud komitmen transparansi informasi publik serta upaya peningkatan kualitas layanan pendidikan,
                        kepemudaan, dan olahraga di Kabupaten Karangasem. Melalui portal ini, masyarakat dapat mengakses
                        berbagai layanan, berita, pengumuman, serta dokumen informasi publik dengan mudah dan cepat.
                    </p>
                    <p class="mt-3 text-sm leading-7 text-slate-700">
                        Kami berharap kehadiran portal ini dapat mempermudah akses informasi bagi seluruh masyarakat.
                        Saran dan masukan yang membangun sangat kami harapkan demi perbaikan pelayanan kami ke depan.
                    </p>
                    <p class="mt-6 text-sm font-semibold text-slate-900">Kepala Dinas,</p>
                    <p class="mt-2 text-sm font-bold text-brand-700">{{ settings('site.short_name', '') }}</p>
                </article>

                <article class="rounded-2xl bg-white p-7 shadow-sm ring-1 ring-slate-200">
                    <h2 class="text-xl font-bold text-slate-900">Visi</h2>
                    <blockquote class="mt-4 border-l-4 border-gold-500 bg-gold-50 p-4 text-sm font-semibold italic leading-7 text-slate-800">
                        "Terwujudnya sumber daya manusia yang unggul, berkarakter, berdaya saing, dan berbudaya menuju
                        Karangasem yang aman, sejahtera, dan bahagia."
                    </blockquote>
                </article>

                <article class="rounded-2xl bg-white p-7 shadow-sm ring-1 ring-slate-200">
                    <h2 class="text-xl font-bold text-slate-900">Misi</h2>
                    <ol class="mt-4 list-decimal space-y-3 pl-5 text-sm leading-7 text-slate-700">
                        <li>Meningkatkan mutu dan pemerataan layanan pendidikan anak usia dini, dasar, dan menengah.</li>
                        <li>Meningkatkan pembinaan dan pengembangan kepemudaan serta prestasi olahraga.</li>
                        <li>Meningkatkan kapasitas dan profesionalisme tenaga pendidik dan kependidikan.</li>
                        <li>Mewujudkan tata kelola pemerintahan dinas yang bersih, transparan, dan akuntabel.</li>
                        <li>Meningkatkan partisipasi masyarakat dan dunia usaha dalam penyelenggaraan pendidikan.</li>
                    </ol>
                </article>
            </div>

            <aside class="space-y-6">
                <div class="rounded-2xl bg-white p-7 shadow-sm ring-1 ring-slate-200">
                    <h3 class="text-base font-bold text-slate-900">Tugas & Fungsi</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600">
                        Melaksanakan urusan pemerintahan daerah di bidang pendidikan, kepemudaan, dan olahraga
                        berdasarkan asas otonomi dan tugas pembantuan.
                    </p>
                </div>
                <div class="rounded-2xl bg-brand-500 p-7 text-white shadow-sm">
                    <h3 class="text-base font-bold">Struktur Organisasi</h3>
                    <p class="mt-3 text-sm leading-6 text-brand-100">
                        Lihat bagan struktur organisasi {{ settings('site.short_name', '') }}.
                    </p>
                    <a href="{{ route('profil.struktur') }}" class="mt-4 inline-flex items-center gap-1 rounded-full bg-white px-4 py-2 text-sm font-bold text-brand-700 transition hover:bg-brand-50">
                        Lihat Struktur
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                </div>
                <div class="rounded-2xl bg-white p-7 shadow-sm ring-1 ring-slate-200">
                    <h3 class="text-base font-bold text-slate-900">Kontak Kami</h3>
                    <ul class="mt-3 space-y-2 text-sm text-slate-600">
                        <li>{{ settings('site.address', '-') }}</li>
                        <li>{{ settings('site.phone', '-') }}</li>
                        <li><a href="mailto:{{ settings('site.email') }}" class="text-brand-600 hover:underline">{{ settings('site.email', '-') }}</a></li>
                    </ul>
                </div>
            </aside>
        </div>
    </section>
@endsection
