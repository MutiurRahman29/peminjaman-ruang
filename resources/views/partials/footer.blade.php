<footer class="border-t border-border bg-[#1f1d1b] text-ivory">
    <div class="page-section py-12 sm:py-16">
        <div class="grid gap-10 border-b border-border/40 pb-10 lg:grid-cols-[1.4fr_0.7fr_0.9fr]">
            <div>
                <a href="{{ route('home') }}" class="group inline-flex items-center gap-3" aria-label="Kembali ke beranda">
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-ivory text-xl text-charcoal transition-transform duration-200 group-hover:scale-105" aria-hidden="true">🙂</span>
                    <span class="block text-sm font-semibold text-ivory">Sistem Pengelolaan Ruang dan Fasilitas</span>
                </a>
                <p class="mt-5 max-w-lg text-sm leading-6 text-[#d1c9c3]">
                    Platform terpadu untuk menemukan ruang, mengecek fasilitas, dan mengikuti proses peminjaman dengan jelas.
                </p>
            </div>

            <div>
                <h2 class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#d1c9c3]">Navigasi</h2>
                <ul class="mt-5 space-y-3 text-sm">
                    @auth
                        @php($currentRole = auth()->user()?->role)
                        <li><a href="{{ route('dashboard') }}" class="text-border transition-colors hover:text-ivory">Dashboard</a></li>
                        @if ($currentRole === \App\Enums\UserRole::Peminjam)
                            <li><a href="{{ route('peminjam.ruangan.index') }}" class="text-border transition-colors hover:text-ivory">Katalog ruangan</a></li>
                            <li><a href="{{ route('peminjam.fasilitas.index') }}" class="text-border transition-colors hover:text-ivory">Inventaris fasilitas</a></li>
                            <li><a href="{{ route('peminjam.peminjaman.index') }}" class="text-border transition-colors hover:text-ivory">Riwayat peminjaman</a></li>
                        @elseif ($currentRole === \App\Enums\UserRole::Petugas)
                            <li><a href="{{ route('petugas.peminjaman.index') }}" class="text-border transition-colors hover:text-ivory">Antrean peminjaman</a></li>
                            <li><a href="{{ route('petugas.peminjaman.history') }}" class="text-border transition-colors hover:text-ivory">Riwayat persetujuan</a></li>
                        @elseif ($currentRole === \App\Enums\UserRole::Admin)
                            <li><a href="{{ route('admin.ruangan.index') }}" class="text-border transition-colors hover:text-ivory">Kelola ruangan</a></li>
                            <li><a href="{{ route('admin.fasilitas.index') }}" class="text-border transition-colors hover:text-ivory">Kelola fasilitas</a></li>
                            <li><a href="{{ route('admin.users.index') }}" class="text-border transition-colors hover:text-ivory">Kelola pengguna</a></li>
                        @endif
                    @else
                        <li><a href="{{ route('home') }}" class="text-border transition-colors hover:text-ivory">Beranda</a></li>
                    @endauth
                </ul>
            </div>
        </div>

        <div class="flex flex-col items-center justify-between gap-3 pt-6 text-xs text-[#a9a29b] sm:flex-row">
            <p>&copy; {{ date('Y') }} Sistem Pengelolaan Ruang dan Fasilitas.</p>
            <p>Ditujukan untuk pengalaman kerja yang sederhana dan efisien.</p>
        </div>
    </div>
</footer>