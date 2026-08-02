@props(['title' => null])

<header class="sticky top-0 z-40 shadow-md">
    <div class="bg-slate-900 text-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-1.5 text-xs sm:px-6 lg:px-8">
            <p class="hidden items-center gap-2 truncate sm:flex">
                <span class="font-semibold text-gold-400">{{ settings('site.tagline', '') }}</span>
                <span class="text-slate-400">{{ settings('site.short_name', '') }}</span>
            </p>
            <p
                class="flex items-center gap-2"
                x-data="clock"
                aria-label="Waktu Indonesia Tengah"
            >
                <svg class="h-3.5 w-3.5 text-gold-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span x-text="now">-</span>
            </p>
        </div>
    </div>

    <div class="bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="{{ settings('site.name', '') }}">
                <img
                    src="{{ asset('images/disdikpora-logo.svg') }}"
                    alt="Logo {{ settings('site.name', 'Disdikpora Karangasem') }}"
                    class="h-12 w-12 shrink-0 object-contain sm:h-14 sm:w-14"
                >
                <div class="leading-tight">
                    <p class="text-[11px] font-medium uppercase tracking-wide text-slate-500">Pemerintah Kabupaten Karangasem</p>
                    <p class="text-sm font-bold text-slate-900 sm:text-base">{{ settings('site.name', '') }}</p>
                    <p class="text-[11px] font-semibold text-brand-600">Dinas Pendidikan, Kepemudaan dan Olahraga</p>
                </div>
            </a>

            <form action="{{ route('berita.index') }}" method="GET" class="hidden w-full max-w-xs items-center md:flex" role="search">
                <label class="sr-only" for="search">Cari berita</label>
                <div class="relative w-full">
                    <input
                        id="search"
                        name="q"
                        type="search"
                        placeholder="Cari berita..."
                        class="w-full rounded-full border-slate-300 py-2 pl-9 pr-4 text-sm focus:border-brand-500 focus:ring-brand-500"
                    >
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </div>
            </form>
        </div>
    </div>

    <nav class="bg-brand-500" x-data="{ open: false }" aria-label="Navigasi utama">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <ul class="hidden items-center divide-x divide-white/20 md:flex">
                    <li>
                        <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'bg-brand-700' : '' }} block px-4 py-3 text-sm font-semibold text-white transition hover:bg-brand-600">Beranda</a>
                    </li>
                    <li x-data="{ profilOpen: false }">
                        <div class="relative">
                            <button
                                type="button"
                                class="flex items-center gap-1 px-4 py-3 text-sm font-semibold text-white transition hover:bg-brand-600"
                                x-on:click="profilOpen = !profilOpen"
                                x-bind:aria-expanded="profilOpen ? 'true' : 'false'"
                            >
                                Profil
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>
                            <div
                                class="absolute left-0 top-full z-50 w-56 origin-top rounded-b-lg border border-t-0 border-slate-200 bg-white py-1 shadow-lg"
                                x-show="profilOpen"
                                x-on:click.away="profilOpen = false"
                                x-transition
                                x-cloak
                            >
                                <a href="{{ route('profil') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-700">Profil Instansi</a>
                                <a href="{{ route('profil.struktur') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-brand-50 hover:text-brand-700">Struktur Organisasi</a>
                            </div>
                        </div>
                    </li>
                    <li><a href="{{ route('layanan.index') }}" class="{{ request()->routeIs('layanan.*') ? 'bg-brand-700' : '' }} block px-4 py-3 text-sm font-semibold text-white transition hover:bg-brand-600">Layanan</a></li>
                    <li><a href="{{ route('sop.index') }}" class="{{ request()->routeIs('sop.*') ? 'bg-brand-700' : '' }} block px-4 py-3 text-sm font-semibold text-white transition hover:bg-brand-600">SOP</a></li>
                    <li><a href="{{ route('ppid.index') }}" class="{{ request()->routeIs('ppid.*') ? 'bg-brand-700' : '' }} block px-4 py-3 text-sm font-semibold text-white transition hover:bg-brand-600">PPID</a></li>
                    <li><a href="{{ route('berita.index') }}" class="{{ request()->routeIs('berita.*') ? 'bg-brand-700' : '' }} block px-4 py-3 text-sm font-semibold text-white transition hover:bg-brand-600">Berita</a></li>
                    <li><a href="{{ route('pengumuman.index') }}" class="{{ request()->routeIs('pengumuman.*') ? 'bg-brand-700' : '' }} block px-4 py-3 text-sm font-semibold text-white transition hover:bg-brand-600">Pengumuman</a></li>
                    <li><a href="{{ route('agenda.index') }}" class="{{ request()->routeIs('agenda.*') ? 'bg-brand-700' : '' }} block px-4 py-3 text-sm font-semibold text-white transition hover:bg-brand-600">Agenda</a></li>
                    <li><a href="{{ route('galeri.index') }}" class="{{ request()->routeIs('galeri.*') ? 'bg-brand-700' : '' }} block px-4 py-3 text-sm font-semibold text-white transition hover:bg-brand-600">Galeri</a></li>
                    <li><a href="{{ route('unduhan.index') }}" class="{{ request()->routeIs('unduhan.*') ? 'bg-brand-700' : '' }} block px-4 py-3 text-sm font-semibold text-white transition hover:bg-brand-600">Unduhan</a></li>
                    <li><a href="{{ route('kontak') }}" class="{{ request()->routeIs('kontak') ? 'bg-brand-700' : '' }} block px-4 py-3 text-sm font-semibold text-white transition hover:bg-brand-600">Kontak</a></li>
                </ul>

                <button
                    type="button"
                    class="inline-flex items-center gap-2 px-3 py-3 text-sm font-semibold text-white md:hidden"
                    x-on:click="open = !open"
                    aria-label="Buka menu navigasi"
                    x-bind:aria-expanded="open ? 'true' : 'false'"
                >
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                    Menu
                </button>
            </div>
        </div>

        <div class="border-t border-white/20 md:hidden" x-show="open" x-transition x-cloak>
            <ul class="space-y-0.5 px-4 py-2">
                <li><a href="{{ route('home') }}" class="block rounded-md px-3 py-2 text-sm font-medium text-white hover:bg-brand-600">Beranda</a></li>
                <li><a href="{{ route('profil') }}" class="block rounded-md px-3 py-2 text-sm font-medium text-white hover:bg-brand-600">Profil Instansi</a></li>
                <li><a href="{{ route('profil.struktur') }}" class="block rounded-md px-3 py-2 text-sm font-medium text-white hover:bg-brand-600">Struktur Organisasi</a></li>
                <li><a href="{{ route('layanan.index') }}" class="block rounded-md px-3 py-2 text-sm font-medium text-white hover:bg-brand-600">Layanan</a></li>
                <li><a href="{{ route('sop.index') }}" class="block rounded-md px-3 py-2 text-sm font-medium text-white hover:bg-brand-600">SOP</a></li>
                <li><a href="{{ route('ppid.index') }}" class="block rounded-md px-3 py-2 text-sm font-medium text-white hover:bg-brand-600">PPID</a></li>
                <li><a href="{{ route('berita.index') }}" class="block rounded-md px-3 py-2 text-sm font-medium text-white hover:bg-brand-600">Berita</a></li>
                <li><a href="{{ route('pengumuman.index') }}" class="block rounded-md px-3 py-2 text-sm font-medium text-white hover:bg-brand-600">Pengumuman</a></li>
                <li><a href="{{ route('agenda.index') }}" class="block rounded-md px-3 py-2 text-sm font-medium text-white hover:bg-brand-600">Agenda</a></li>
                <li><a href="{{ route('galeri.index') }}" class="block rounded-md px-3 py-2 text-sm font-medium text-white hover:bg-brand-600">Galeri</a></li>
                <li><a href="{{ route('unduhan.index') }}" class="block rounded-md px-3 py-2 text-sm font-medium text-white hover:bg-brand-600">Unduhan</a></li>
                <li><a href="{{ route('kontak') }}" class="block rounded-md px-3 py-2 text-sm font-medium text-white hover:bg-brand-600">Kontak</a></li>
            </ul>
        </div>
    </nav>
</header>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('clock', () => ({
            now: '',
            init() {
                this.tick();
                setInterval(() => this.tick(), 1000);
            },
            tick() {
                this.now = new Intl.DateTimeFormat('id-ID', {
                    timeZone: 'Asia/Makassar',
                    weekday: 'long',
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                }).format(new Date());
            },
        }));
    });
</script>
