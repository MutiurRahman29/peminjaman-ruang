@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <main class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
        @php($role = auth()->user()?->role)

        @if ($role === \App\Enums\UserRole::Admin)
            {{-- Admin Header --}}
            <section class="border-b border-border pb-12">
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-taupe">Dashboard admin</p>
                <div class="mt-5 grid gap-8 lg:grid-cols-[1fr_auto] lg:items-end">
                    <div>
                        <h1 class="max-w-3xl text-4xl font-bold tracking-[-0.04em] text-charcoal sm:text-5xl">
                            Selamat datang, {{ auth()->user()?->nama }}.
                        </h1>
                        <p class="mt-5 max-w-2xl text-base leading-7 text-muted">
                            Pantau kondisi sistem, kelola data utama, dan lanjutkan pengelolaan peminjaman dari satu tempat.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('admin.ruangan.index') }}" class="action-button-primary">Kelola ruangan</a>
                        <a href="{{ route('admin.peminjaman.index') }}" class="action-button-secondary">Lihat laporan</a>
                    </div>
                </div>
            </section>

            @php($notifikasi = auth()->user()?->unreadNotifications()->where('type', \App\Notifications\PeminjamanBaru::class)->latest()->limit(5)->get())
            @if ($notifikasi->isNotEmpty())
                <section class="mt-10 border border-amber-700/20 bg-amber-700/10 p-5 sm:p-6" aria-labelledby="notifikasi-peminjaman-baru">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-700">Notifikasi baru</p>
                            <h2 id="notifikasi-peminjaman-baru" class="mt-2 text-xl font-bold text-charcoal">Pengajuan peminjaman menunggu</h2>
                        </div>
                        <a href="{{ route('admin.peminjaman.index') }}" class="text-sm font-semibold text-taupe hover:text-charcoal">Lihat semua pengajuan →</a>
                    </div>
                    <ul class="mt-5 space-y-3">
                        @foreach ($notifikasi as $notifikasiItem)
                            <li class="flex flex-col gap-2 rounded-xl border border-amber-700/20 bg-cream p-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="font-semibold text-charcoal">{{ $notifikasiItem->data['nama_pemohon'] }} mengajukan peminjaman</p>
                                    <p class="mt-1 text-sm text-muted">{{ $notifikasiItem->data['nama_ruangan'] }} · {{ $notifikasiItem->data['tanggal'] }}</p>
                                </div>
                                <a href="{{ route('admin.notifications.open', $notifikasiItem->id) }}" class="inline-flex font-semibold text-taupe hover:text-charcoal">Detail pengajuan →</a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            {{-- Ringkasan sistem --}}
            <section class="mt-10">
                <div class="flex items-end justify-between gap-6">
                    <div>
                        <p class="section-eyebrow">Ringkasan sistem</p>
                        <h2 class="text-2xl font-bold text-charcoal">Kondisi saat ini</h2>
                    </div>
                    <p class="hidden text-sm text-muted sm:block">Data real-time dari basis sistem</p>
                </div>

                <div class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="border border-border bg-cream p-5">
                        <p class="text-3xl font-bold text-charcoal">{{ \App\Models\Ruangan::count() }}</p>
                        <p class="mt-2 text-sm text-muted">Ruangan terdaftar</p>
                    </div>
                    <div class="border border-border bg-cream p-5">
                        <p class="text-3xl font-bold text-charcoal">{{ \App\Models\Fasilitas::count() }}</p>
                        <p class="mt-2 text-sm text-muted">Fasilitas tersedia</p>
                    </div>
                    <div class="border border-border bg-cream p-5">
                        <p class="text-3xl font-bold text-charcoal">{{ \App\Models\Peminjaman::where('status', \App\Enums\StatusPeminjaman::Menunggu)->count() }}</p>
                        <p class="mt-2 text-sm text-muted">Pengajuan menunggu</p>
                    </div>
                    <div class="border border-border bg-cream p-5">
                        <p class="text-3xl font-bold text-charcoal">{{ \App\Models\Peminjaman::where('status', \App\Enums\StatusPeminjaman::Disetujui)->count() }}</p>
                        <p class="mt-2 text-sm text-muted">Peminjaman aktif</p>
                    </div>
                </div>
            </section>

            {{-- Pengelolaan Administrasi --}}
            <section class="mt-16 border-t border-border pt-10">
                <div class="mb-8">
                    <p class="section-eyebrow">Pengelolaan administrasi</p>
                    <h2 class="text-2xl font-bold text-charcoal">Kelola seluruh data sistem</h2>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <a href="{{ route('admin.ruangan.index') }}" class="border border-border bg-cream p-6 transition hover:border-border">
                        <h3 class="text-lg font-bold text-charcoal">Kelola Ruangan</h3>
                        <p class="mt-2 text-sm text-muted">Master data dan status operasional.</p>
                        <span class="mt-5 inline-flex text-sm font-semibold text-taupe">Buka ruangan <span class="ml-2" aria-hidden="true">→</span></span>
                    </a>
                    <a href="{{ route('admin.fasilitas.index') }}" class="border border-border bg-cream p-6 transition hover:border-border">
                        <h3 class="text-lg font-bold text-charcoal">Kelola Fasilitas</h3>
                        <p class="mt-2 text-sm text-muted">Inventaris dan kondisi fasilitas.</p>
                        <span class="mt-5 inline-flex text-sm font-semibold text-taupe">Buka fasilitas <span class="ml-2" aria-hidden="true">→</span></span>
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="border border-border bg-cream p-6 transition hover:border-border">
                        <h3 class="text-lg font-bold text-charcoal">Kelola Pengguna</h3>
                        <p class="mt-2 text-sm text-muted">Kelola akun dan hak akses.</p>
                        <span class="mt-5 inline-flex text-sm font-semibold text-taupe">Buka pengguna <span class="ml-2" aria-hidden="true">→</span></span>
                    </a>
                    <a href="{{ route('admin.peminjaman.index') }}" class="border border-border bg-cream p-6 transition hover:border-border">
                        <h3 class="text-lg font-bold text-charcoal">Laporan Peminjaman</h3>
                        <p class="mt-2 text-sm text-muted">Pantau aktivitas dan status peminjaman.</p>
                        <span class="mt-5 inline-flex text-sm font-semibold text-taupe">Buka laporan <span class="ml-2" aria-hidden="true">→</span></span>
                    </a>
                </div>
            </section>

        @elseif ($role === \App\Enums\UserRole::Petugas)
            {{-- Petugas Header --}}
            <section class="border-b border-border pb-12">
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-taupe">Dashboard Petugas</p>
                <div class="mt-5 grid gap-8 lg:grid-cols-[1fr_auto] lg:items-end">
                    <div>
                        <h1 class="max-w-3xl text-4xl font-bold tracking-[-0.04em] text-charcoal sm:text-5xl">
                            Selamat datang, {{ auth()->user()?->nama }}.
                        </h1>
                        <p class="mt-5 max-w-2xl text-base leading-7 text-muted">
                            Kelola proses pemeriksaan dan persetujuan pengajuan peminjaman ruangan dan fasilitas.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('petugas.peminjaman.index') }}" class="action-button-primary">Antrean Peminjaman</a>
                        <a href="{{ route('petugas.peminjaman.history') }}" class="action-button-secondary">Riwayat Persetujuan</a>
                    </div>
                </div>
            </section>

            {{-- Ringkasan operasional --}}
            <section class="mt-10">
                <div class="flex items-end justify-between gap-6">
                    <div>
                        <p class="section-eyebrow">Ringkasan operasional</p>
                        <h2 class="text-2xl font-bold text-charcoal">Kondisi antrean</h2>
                    </div>
                </div>

                <div class="mt-7 grid gap-4 sm:grid-cols-3">
                    <div class="border border-border bg-cream p-5">
                        <p class="text-3xl font-bold text-charcoal">{{ \App\Models\Peminjaman::where('status', \App\Enums\StatusPeminjaman::Menunggu)->count() }}</p>
                        <p class="mt-2 text-sm text-muted">Menunggu persetujuan</p>
                    </div>
                    <div class="border border-border bg-cream p-5">
                        <p class="text-3xl font-bold text-charcoal">{{ \App\Models\Peminjaman::where('status', \App\Enums\StatusPeminjaman::Disetujui)->count() }}</p>
                        <p class="mt-2 text-sm text-muted">Sedang aktif disetujui</p>
                    </div>
                    <div class="border border-border bg-cream p-5">
                        <p class="text-3xl font-bold text-charcoal">{{ \App\Models\Peminjaman::whereIn('status', [\App\Enums\StatusPeminjaman::Selesai, \App\Enums\StatusPeminjaman::Ditolak])->count() }}</p>
                        <p class="mt-2 text-sm text-muted">Total selesai diproses</p>
                    </div>
                </div>
            </section>

            {{-- Menu Operasional --}}
            <section class="mt-16 border-t border-border pt-10">
                <div class="mb-8">
                    <p class="section-eyebrow">Operasional</p>
                    <h2 class="text-2xl font-bold text-charcoal">Kelola proses persetujuan</h2>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <a href="{{ route('petugas.peminjaman.index') }}" class="border border-border bg-cream p-6 transition hover:border-border">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-taupe">Menunggu tindakan</p>
                        <h3 class="mt-2 text-xl font-bold text-charcoal">Antrean Peminjaman</h3>
                        <p class="mt-2 text-sm leading-6 text-muted">Periksa dan proses pengajuan peminjaman yang masuk.</p>
                        <span class="mt-5 inline-flex text-sm font-semibold text-taupe">Buka antrean <span class="ml-2" aria-hidden="true">→</span></span>
                    </a>

                    <a href="{{ route('petugas.peminjaman.history') }}" class="border border-border bg-cream p-6 transition hover:border-border">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-taupe">Arsip</p>
                        <h3 class="mt-2 text-xl font-bold text-charcoal">Riwayat Persetujuan</h3>
                        <p class="mt-2 text-sm leading-6 text-muted">Tinjau seluruh pengajuan yang telah diproses.</p>
                        <span class="mt-5 inline-flex text-sm font-semibold text-taupe">Buka riwayat <span class="ml-2" aria-hidden="true">→</span></span>
                    </a>
                </div>
            </section>

        @else
            {{-- Peminjam Header --}}
            <section class="border-b border-border pb-12">
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-taupe">Dashboard Peminjam</p>
                <div class="mt-5 grid gap-8 lg:grid-cols-[1fr_auto] lg:items-end">
                    <div>
                        <h1 class="max-w-3xl text-4xl font-bold tracking-[-0.04em] text-charcoal sm:text-5xl">
                            Selamat datang, {{ auth()->user()?->nama }}.
                        </h1>
                        <p class="mt-5 max-w-2xl text-base leading-7 text-muted">
                            Temukan ruangan yang sesuai, cek fasilitas yang tersedia, dan pantau status peminjaman Anda.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('peminjam.peminjaman.create') }}" class="action-button-primary">Ajukan peminjaman</a>
                        <a href="{{ route('peminjam.peminjaman.index') }}" class="action-button-secondary">Riwayat peminjaman</a>
                    </div>
                </div>
            </section>

            {{-- Menu Peminjam --}}
            <section class="mt-16 border-t border-border pt-10">
                <div class="mb-8">
                    <p class="section-eyebrow">Aktivitas</p>
                    <h2 class="text-2xl font-bold text-charcoal">Eksplorasi dan peminjaman</h2>
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <a href="{{ route('peminjam.peminjaman.create') }}" class="border border-border bg-cream p-6 transition hover:border-border">
                        <h3 class="text-lg font-bold text-charcoal">Ajukan Peminjaman</h3>
                        <p class="mt-2 text-sm text-muted">Buat permohonan baru untuk pemakaian ruangan dan fasilitas.</p>
                        <span class="mt-5 inline-flex text-sm font-semibold text-taupe">Mulai pengajuan <span class="ml-2" aria-hidden="true">→</span></span>
                    </a>
                    <a href="{{ route('peminjam.ruangan.index') }}" class="border border-border bg-cream p-6 transition hover:border-border">
                        <h3 class="text-lg font-bold text-charcoal">Katalog Ruangan</h3>
                        <p class="mt-2 text-sm text-muted">Lihat ruangan yang tersedia untuk digunakan.</p>
                        <span class="mt-5 inline-flex text-sm font-semibold text-taupe">Lihat ruangan <span class="ml-2" aria-hidden="true">→</span></span>
                    </a>
                    <a href="{{ route('peminjam.fasilitas.index') }}" class="border border-border bg-cream p-6 transition hover:border-border">
                        <h3 class="text-lg font-bold text-charcoal">Katalog Fasilitas</h3>
                        <p class="mt-2 text-sm text-muted">Lihat fasilitas yang tersedia untuk peminjaman.</p>
                        <span class="mt-5 inline-flex text-sm font-semibold text-taupe">Lihat fasilitas <span class="ml-2" aria-hidden="true">→</span></span>
                    </a>
                </div>
            </section>
        @endif
    </main>
@endsection
