<footer class="bg-slate-900 text-slate-300">
    @php
        $relatedLinks = \App\Models\RelatedLink::active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    @endphp

    <div class="mx-auto grid max-w-7xl grid-cols-2 gap-x-6 gap-y-6 px-4 py-8 sm:px-6 md:grid-cols-4 lg:px-8">
        <div class="col-span-2 md:col-span-1">
            <div class="flex items-center gap-3">
                <img
                    src="{{ asset('images/disdikpora-logo.svg') }}"
                    alt="Logo {{ settings('site.name', 'Disdikpora Karangasem') }}"
                    class="h-24 w-24 object-contain"
                    loading="lazy"
                >
                <div>
                    <p class="text-sm font-bold text-white">{{ settings('site.name', '') }}</p>
                    <p class="text-xs text-gold-400">{{ settings('site.tagline', '') }}</p>
                </div>
            </div>
            <p class="mt-3 text-sm leading-snug">
                Portal resmi Dinas Pendidikan, Kepemudaan dan Olahraga Kabupaten Karangasem. Informasi publik dan layanan disajikan secara transparan dan akuntabel.
            </p>
        </div>

        <div class="col-span-2 grid grid-cols-2 gap-x-4 gap-y-4 md:col-span-2">
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wide text-white">Tautan Cepat</h3>
                <ul class="mt-3 grid grid-cols-1 gap-x-3 gap-y-1.5 text-sm sm:grid-cols-2">
                    <li><a href="{{ route('layanan.index') }}" class="hover:text-gold-400">Katalog Layanan</a></li>
                    <li><a href="{{ route('sop.index') }}" class="hover:text-gold-400">Dokumen SOP</a></li>
                    <li><a href="{{ route('ppid.index') }}" class="hover:text-gold-400">Informasi PPID</a></li>
                    <li><a href="{{ route('berita.index') }}" class="hover:text-gold-400">Berita</a></li>
                    <li><a href="{{ route('unduhan.index') }}" class="hover:text-gold-400">Pusat Unduhan</a></li>
                    <li><a href="{{ route('galeri.index') }}" class="hover:text-gold-400">Galeri</a></li>
                    <li><a href="{{ route('agenda.index') }}" class="hover:text-gold-400">Agenda</a></li>
                    <li><a href="{{ route('kontak') }}" class="hover:text-gold-400">Hubungi Kami</a></li>
                </ul>
            </div>

        <div>
            <h3 class="text-sm font-semibold uppercase tracking-wide text-white">Kontak</h3>
            <ul class="mt-3 space-y-2 text-sm">
                <li class="flex gap-2">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-gold-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                    </svg>
                    <span>{{ settings('site.address', '-') }}</span>
                </li>
                <li class="flex gap-2">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-gold-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                    <a href="mailto:{{ settings('site.email') }}" class="break-all hover:text-gold-400">{{ settings('site.email', '-') }}</a>
                </li>
                <li class="flex gap-2">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-gold-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                    </svg>
                    <span>{{ settings('site.phone', '-') }}</span>
                </li>
            </ul>
        </div>
        </div>

        <div class="col-span-2 md:col-span-1">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-white">Tautan Terkait</h3>
            <ul class="mt-3 grid grid-flow-col grid-cols-2 gap-x-3 gap-y-1.5 text-sm">
                @forelse ($relatedLinks as $link)
                    <li>
                        <a
                            href="{{ $link->url }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-1.5 hover:text-gold-400"
                        >
                            {{ $link->name }}
                            <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                            </svg>
                        </a>
                    </li>
                @empty
                    <li class="col-span-2 text-xs text-slate-500">Belum tersedia.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="border-t border-white/10">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-2 px-4 py-3 text-xs sm:flex-row sm:px-6 lg:px-8">
            <p>&copy; {{ now()->year }} {{ settings('site.name', '') }}</p>
            <p>Pemerintah Kabupaten Karangasem</p>
        </div>
    </div>
</footer>
