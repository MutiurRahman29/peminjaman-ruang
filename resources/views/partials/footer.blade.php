<footer class="border-t border-border bg-[#1f1d1b] text-ivory">
    <div class="page-section py-12 sm:py-16">
        <div class="grid gap-10 border-b border-border/40 pb-10 sm:grid-cols-2 lg:grid-cols-[minmax(0,0.85fr)_minmax(0,0.9fr)_minmax(0,1.2fr)_minmax(0,0.95fr)]">
            <div class="max-w-sm">
                <a href="{{ route('home') }}" class="group inline-flex items-center gap-3" aria-label="Kembali ke beranda">
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-ivory text-xl text-charcoal transition-transform duration-200 group-hover:scale-105" aria-hidden="true">🙂</span>
                    <span class="block text-sm font-semibold text-ivory">Ruang &amp; Fasilitas</span>
                </a>
                <p class="mt-4 text-sm leading-6 text-[#d1c9c3]">
                    Sistem informasi peminjaman ruang dan fasilitas.
                </p>
            </div>

            <div>
                <h2 class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#d1c9c3]">Navigasi</h2>
                <ul class="mt-5 space-y-3 text-sm">
                    <li><a href="{{ route('home') }}#hero" class="text-border transition-colors hover:text-ivory">Beranda</a></li>
                    <li><a href="{{ route('home') }}#tentang" class="text-border transition-colors hover:text-ivory">Tentang</a></li>
                    <li><a href="{{ route('home') }}#fitur" class="text-border transition-colors hover:text-ivory">Fitur</a></li>
                    <li><a href="{{ route('home') }}#cara-kerja" class="text-border transition-colors hover:text-ivory">Cara Kerja</a></li>
                    <li><a href="{{ route('home') }}#siap-mulai" class="text-border transition-colors hover:text-ivory">Mulai</a></li>
                </ul>
            </div>

            <div>
                <h2 class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#d1c9c3]">Halaman</h2>
                <ul class="mt-5 space-y-3 text-sm">
                    @auth
                        @php($currentRole = auth()->user()?->role)
                        <li><a href="{{ route('dashboard') }}" class="text-border transition-colors hover:text-ivory">Dashboard</a></li>
                        @if ($currentRole === \App\Enums\UserRole::Peminjam)
                            <li><a href="{{ route('peminjam.ruangan.index') }}" class="text-border transition-colors hover:text-ivory">Katalog ruangan</a></li>
                            <li><a href="{{ route('peminjam.fasilitas.index') }}" class="text-border transition-colors hover:text-ivory">Katalog fasilitas</a></li>
                            <li><a href="{{ route('peminjam.peminjaman.create') }}" class="text-border transition-colors hover:text-ivory">Ajukan peminjaman</a></li>
                            <li><a href="{{ route('peminjam.peminjaman.index') }}" class="text-border transition-colors hover:text-ivory">Riwayat peminjaman</a></li>
                            <li><a href="{{ route('peminjam.peminjaman.access') }}" class="text-border transition-colors hover:text-ivory">Cek status peminjaman</a></li>
                        @elseif ($currentRole === \App\Enums\UserRole::Petugas)
                            <li><a href="{{ route('petugas.peminjaman.index') }}" class="text-border transition-colors hover:text-ivory">Antrean peminjaman</a></li>
                            <li><a href="{{ route('petugas.peminjaman.history') }}" class="text-border transition-colors hover:text-ivory">Riwayat persetujuan</a></li>
                        @elseif ($currentRole === \App\Enums\UserRole::Admin)
                            <li><a href="{{ route('admin.peminjaman.index') }}" class="text-border transition-colors hover:text-ivory">Laporan peminjaman</a></li>
                            <li><a href="{{ route('admin.ruangan.index') }}" class="text-border transition-colors hover:text-ivory">Kelola ruangan</a></li>
                            <li><a href="{{ route('admin.fasilitas.index') }}" class="text-border transition-colors hover:text-ivory">Kelola fasilitas</a></li>
                            <li><a href="{{ route('admin.users.index') }}" class="text-border transition-colors hover:text-ivory">Kelola pengguna</a></li>
                        @endif
                    @else
                        <li><a href="{{ route('peminjam.ruangan.index') }}" class="text-border transition-colors hover:text-ivory">Katalog ruangan</a></li>
                        <li><a href="{{ route('peminjam.fasilitas.index') }}" class="text-border transition-colors hover:text-ivory">Katalog fasilitas</a></li>
                        <li><a href="{{ route('peminjam.peminjaman.create') }}" class="text-border transition-colors hover:text-ivory">Ajukan peminjaman</a></li>
                        <li><a href="{{ route('peminjam.peminjaman.access') }}" class="text-border transition-colors hover:text-ivory">Cek status peminjaman</a></li>
                    @endauth
                </ul>
            </div>

            <div>
                <h2 class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#d1c9c3]">Tim</h2>
                <ul class="mt-5 space-y-3 text-sm leading-5 text-border">
                    <li><a target="_blank" href="https://www.instagram.com/samil_bsyv?exln=MWJlM204bmF2cWdkZA==" class="text-border transition-colors hover:text-ivory">Syamil Cholid Atsani</a></li>
                    <li><a target="_blank" href="https://www.instagram.com/acidd_ux?xtok=NHN6eTVobGNrY2hr" class="text-border transition-colors hover:text-ivory">Muhammad Al Rasyid</a></li>
                    <li><a target="_blank" href="https://www.instagram.com/lintyurrhmn?rpxt=MWU5MnkwaWN1cGwyMw==" class="text-border transition-colors hover:text-ivory">Lintang Mutiur Rahman</a></li>
                    <li><a target="_blank" href="https://www.instagram.com/arjunhfdzz?vrfl=c3MwZW1uYWN2cDUz" class="text-border transition-colors hover:text-ivory">Arjun Hafidz Meiansya</a></li>
                </ul>
            </div>
        </div>

        <div class="flex flex-col items-center justify-between gap-3 pt-6 text-xs text-[#a9a29b] sm:flex-row">
            <p>&copy; {{ date('Y') }} PKL BBPPMPV BMTI ELIT</p>
            <p>Semoga Project Ini Dapat Membantu Terimakasih.</p>
        </div>
    </div>
</footer>