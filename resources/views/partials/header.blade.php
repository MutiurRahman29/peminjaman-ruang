<header
    class="fixed inset-x-0 top-0 z-50 w-full text-charcoal transition-all duration-500 ease-out"
    x-data="{ open: false, scrolled: false }"
    x-bind:class="scrolled ? 'top-4' : 'top-0'"
    x-on:scroll.window="scrolled = window.scrollY > 8"
>
    <div
        class="mx-auto flex w-full items-center justify-between gap-4 px-4 transition-all duration-500 sm:px-6 lg:px-8"
        x-bind:class="scrolled ? 'mx-4 h-16 max-w-[calc(100%-2rem)] rounded-2xl border border-border bg-ivory px-4 shadow-[0_16px_38px_rgba(41,39,35,0.08)] backdrop-blur-xl sm:px-5 lg:px-6' : 'mx-0 h-20 max-w-full rounded-none border-b border-border bg-ivory backdrop-blur-md sm:px-6 lg:px-8'"
    >
        <a href="{{ route('home') }}" class="group flex min-w-0 items-center gap-3" aria-label="Beranda Sistem Pengelolaan Ruang dan Fasilitas">
            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-charcoal text-xl text-ivory transition-transform duration-200 group-hover:scale-105 sm:h-10 sm:w-10" aria-hidden="true">🙂</span>
            <span class="flex flex-col">
                <span class="text-[10px] font-bold uppercase tracking-[0.18em] text-taupe leading-tight">Sistem Pengelolaan</span>
                <span class="text-xs font-semibold leading-tight text-charcoal sm:text-sm">Ruang &amp; Fasilitas</span>
            </span>
        </a>

            <nav class="ml-auto hidden items-center lg:flex" aria-label="Navigasi utama">
                @auth
                    @php($currentRole = auth()->user()?->role)
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}" {{ request()->routeIs('dashboard') ? 'aria-current="page"' : '' }}>Dashboard</a>
                    @if ($currentRole === \App\Enums\UserRole::Peminjam)
                        <a href="{{ route('peminjam.ruangan.index') }}" class="nav-link {{ request()->routeIs('peminjam.ruangan.index') ? 'nav-link-active' : '' }}" {{ request()->routeIs('peminjam.ruangan.index') ? 'aria-current="page"' : '' }}>Ruangan</a>
                        <a href="{{ route('peminjam.fasilitas.index') }}" class="nav-link {{ request()->routeIs('peminjam.fasilitas.index') ? 'nav-link-active' : '' }}" {{ request()->routeIs('peminjam.fasilitas.index') ? 'aria-current="page"' : '' }}>Fasilitas</a>
                        <a href="{{ route('peminjam.peminjaman.index') }}" class="nav-link {{ request()->routeIs('peminjam.peminjaman.*') ? 'nav-link-active' : '' }}" {{ request()->routeIs('peminjam.peminjaman.*') ? 'aria-current="page"' : '' }}>Peminjaman</a>
                    @endif
                    @if ($currentRole === \App\Enums\UserRole::Petugas)
                        <a href="{{ route('petugas.peminjaman.index') }}" class="nav-link {{ request()->routeIs('petugas.peminjaman.index') ? 'nav-link-active' : '' }}" {{ request()->routeIs('petugas.peminjaman.index') ? 'aria-current="page"' : '' }}>Antrean</a>
                        <a href="{{ route('petugas.peminjaman.history') }}" class="nav-link {{ request()->routeIs('petugas.peminjaman.history') ? 'nav-link-active' : '' }}" {{ request()->routeIs('petugas.peminjaman.history') ? 'aria-current="page"' : '' }}>Riwayat</a>
                    @endif
                    @if ($currentRole === \App\Enums\UserRole::Admin)
                        <a href="{{ route('admin.ruangan.index') }}" class="nav-link {{ request()->routeIs('admin.ruangan.*') ? 'nav-link-active' : '' }}" {{ request()->routeIs('admin.ruangan.*') ? 'aria-current="page"' : '' }}>Ruangan</a>
                        <a href="{{ route('admin.fasilitas.index') }}" class="nav-link {{ request()->routeIs('admin.fasilitas.*') ? 'nav-link-active' : '' }}" {{ request()->routeIs('admin.fasilitas.*') ? 'aria-current="page"' : '' }}>Fasilitas</a>
                        <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'nav-link-active' : '' }}" {{ request()->routeIs('admin.users.*') ? 'aria-current="page"' : '' }}>Pengguna</a>
                        <a href="{{ route('admin.peminjaman.index') }}" class="nav-link {{ request()->routeIs('admin.peminjaman.*') ? 'nav-link-active' : '' }}" {{ request()->routeIs('admin.peminjaman.*') ? 'aria-current="page"' : '' }}>Laporan</a>
                    @endif
                @else
                    <a href="{{ route('home') }}#hero" class="nav-link landing-section-link">Beranda</a>
                    <a href="{{ route('home') }}#tentang" class="nav-link landing-section-link">Tentang</a>
                    <a href="{{ route('home') }}#fitur" class="nav-link landing-section-link">Fitur</a>
                    <a href="{{ route('home') }}#cara-kerja" class="nav-link landing-section-link">Cara Kerja</a>
                    <a href="{{ route('home') }}#siap-mulai" class="nav-link landing-section-link">Mulai</a>
                    <a href="{{ route('peminjam.peminjaman.access') }}" class="action-button-secondary bg-cream/80 px-4 py-2 hover:bg-cream">Cek Peminjaman</a>
                @endauth
            </nav>

            <div class="flex items-center gap-2">
                @auth
                    <div class="hidden items-center gap-3 px-2 py-1 lg:flex">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-charcoal text-xs font-bold text-ivory">{{ strtoupper(substr(auth()->user()?->nama ?? 'U', 0, 1)) }}</span>
                        <div class="min-w-0">
                            <span class="block max-w-36 truncate text-sm font-medium text-charcoal" title="{{ auth()->user()?->nama }}">{{ auth()->user()?->nama }}</span>
                            <span class="block text-[10px] font-semibold uppercase tracking-[0.12em] text-taupe">{{ auth()->user()?->role?->value ?? '' }}</span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                        @csrf
                        <button type="submit" class="action-button-secondary px-4 py-2 text-xs">Keluar</button>
                    </form>
                @endauth

                <button
                    type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-border bg-cream text-charcoal shadow-[0_8px_18px_rgba(41,39,35,0.08)] transition-all duration-200 hover:-translate-y-0.5 hover:border-border hover:bg-ivory active:scale-95 lg:hidden"
                    aria-label="{{ $open ?? 'Buka' }} menu navigasi"
                    aria-controls="mobile-navigation"
                    aria-expanded="false"
                    x-on:click="open = !open"
                    x-bind:aria-expanded="open"
                    x-bind:aria-label="open ? 'Tutup menu navigasi' : 'Buka menu navigasi'"
                    x-bind:class="open ? 'rotate-180 scale-110 bg-ivory' : ''"
                >
                    <svg class="h-5 w-5 transition-transform duration-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" x-show="!open"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18" x-show="open"/>
                    </svg>
                </button>
            </div>
        </div>

        <nav id="mobile-navigation" x-show="open" x-transition:enter="mobile-menu-enter" x-transition:leave="mobile-menu-leave" @click.outside="open = false" class="mobile-menu border-t border-border bg-ivory py-3 shadow-[0_12px_30px_rgba(41,39,35,0.06)] backdrop-blur-xl lg:hidden" aria-label="Navigasi mobile">
            <div class="mx-auto flex max-w-7xl flex-col gap-1 px-4 sm:px-6">
                @auth
                    <a href="{{ route('dashboard') }}" x-on:click="open = false" class="rounded-xl px-4 py-3 text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-cream text-charcoal' : 'text-muted hover:bg-cream hover:text-charcoal' }}">Dashboard</a>
                    @if (auth()->user()?->role === \App\Enums\UserRole::Peminjam)
                        <a href="{{ route('peminjam.ruangan.index') }}" x-on:click="open = false" class="rounded-xl px-4 py-3 text-sm font-medium {{ request()->routeIs('peminjam.ruangan.index') ? 'bg-cream text-charcoal' : 'text-muted hover:bg-cream hover:text-charcoal' }}">Ruangan</a>
                        <a href="{{ route('peminjam.fasilitas.index') }}" x-on:click="open = false" class="rounded-xl px-4 py-3 text-sm font-medium {{ request()->routeIs('peminjam.fasilitas.index') ? 'bg-cream text-charcoal' : 'text-muted hover:bg-cream hover:text-charcoal' }}">Fasilitas</a>
                        <a href="{{ route('peminjam.peminjaman.index') }}" x-on:click="open = false" class="rounded-xl px-4 py-3 text-sm font-medium {{ request()->routeIs('peminjam.peminjaman.*') ? 'bg-cream text-charcoal' : 'text-muted hover:bg-cream hover:text-charcoal' }}">Peminjaman</a>
                    @endif
                    @if (auth()->user()?->role === \App\Enums\UserRole::Petugas)
                        <a href="{{ route('petugas.peminjaman.index') }}" x-on:click="open = false" class="rounded-xl px-4 py-3 text-sm font-medium {{ request()->routeIs('petugas.peminjaman.index') ? 'bg-cream text-charcoal' : 'text-muted hover:bg-cream hover:text-charcoal' }}">Antrean</a>
                        <a href="{{ route('petugas.peminjaman.history') }}" x-on:click="open = false" class="rounded-xl px-4 py-3 text-sm font-medium {{ request()->routeIs('petugas.peminjaman.history') ? 'bg-cream text-charcoal' : 'text-muted hover:bg-cream hover:text-charcoal' }}">Riwayat</a>
                    @endif
                    @if (auth()->user()?->role === \App\Enums\UserRole::Admin)
                        <a href="{{ route('admin.ruangan.index') }}" x-on:click="open = false" class="rounded-xl px-4 py-3 text-sm font-medium {{ request()->routeIs('admin.ruangan.*') ? 'bg-cream text-charcoal' : 'text-muted hover:bg-cream hover:text-charcoal' }}">Ruangan</a>
                        <a href="{{ route('admin.fasilitas.index') }}" x-on:click="open = false" class="rounded-xl px-4 py-3 text-sm font-medium {{ request()->routeIs('admin.fasilitas.*') ? 'bg-cream text-charcoal' : 'text-muted hover:bg-cream hover:text-charcoal' }}">Fasilitas</a>
                        <a href="{{ route('admin.users.index') }}" x-on:click="open = false" class="rounded-xl px-4 py-3 text-sm font-medium {{ request()->routeIs('admin.users.*') ? 'bg-cream text-charcoal' : 'text-muted hover:bg-cream hover:text-charcoal' }}">Pengguna</a>
                        <a href="{{ route('admin.peminjaman.index') }}" x-on:click="open = false" class="rounded-xl px-4 py-3 text-sm font-medium {{ request()->routeIs('admin.peminjaman.*') ? 'bg-cream text-charcoal' : 'text-muted hover:bg-cream hover:text-charcoal' }}">Laporan</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="pt-3">
                        @csrf
                        <button type="submit" class="action-button-primary w-full">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('home') }}#hero" x-on:click="open = false" class="rounded-xl px-4 py-3 text-sm font-medium {{ request()->is('/') ? 'bg-cream text-charcoal' : 'text-muted hover:bg-cream hover:text-charcoal' }}">Beranda</a>
                    <a href="{{ route('home') }}#tentang" x-on:click="open = false" class="rounded-xl px-4 py-3 text-sm font-medium text-muted hover:bg-cream hover:text-charcoal">Tentang</a>
                    <a href="{{ route('home') }}#fitur" x-on:click="open = false" class="rounded-xl px-4 py-3 text-sm font-medium text-muted hover:bg-cream hover:text-charcoal">Fitur</a>
                    <a href="{{ route('home') }}#cara-kerja" x-on:click="open = false" class="rounded-xl px-4 py-3 text-sm font-medium text-muted hover:bg-cream hover:text-charcoal">Cara Kerja</a>
                    <a href="{{ route('home') }}#siap-mulai" x-on:click="open = false" class="rounded-xl px-4 py-3 text-sm font-medium text-muted hover:bg-cream hover:text-charcoal">Mulai</a>
                    <a href="{{ route('peminjam.peminjaman.access') }}" x-on:click="open = false" class="rounded-xl px-4 py-3 text-sm font-medium text-muted hover:bg-cream hover:text-charcoal">Cek Peminjaman</a>
                @endauth
            </div>
        </nav>
    </div>
</header>
