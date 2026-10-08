@extends('layouts.app')

@section('title', 'Detail Laporan Peminjaman')

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">

        {{-- Page Header & Nav --}}
        <div class="mb-8 animate-fade-up animate-duration-[600ms] animate-ease-out">
            <nav class="mb-3 flex items-center gap-2 text-xs font-medium text-muted">
                <a href="{{ route('dashboard') }}" class="transition-colors hover:text-taupe">Dashboard</a>
                <span>/</span>
                <a href="{{ route('admin.peminjaman.index') }}" class="transition-colors hover:text-taupe">Laporan Peminjaman</a>
                <span>/</span>
                <span class="text-charcoal">Detail</span>
            </nav>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-charcoal sm:text-3xl">
                        Detail Peminjaman
                    </h1>
                    <p class="mt-1 text-sm text-muted">
                        Rincian informasi lengkap permohonan peminjaman ruangan.
                    </p>
                </div>

                {{-- Back Link Button --}}
                <a
                    href="{{ route('admin.peminjaman.index') }}"
                    class="inline-flex items-center gap-2 self-start rounded-xl border border-border bg-ivory px-4 py-2.5 text-xs font-medium text-muted transition-all hover:border-border hover:bg-cream hover:text-charcoal sm:self-auto"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    <span>Kembali ke Laporan</span>
                </a>
            </div>
        </div>

        {{-- Status Header Card --}}
        @php
            $st = strtolower($peminjaman->status->value);
            $statusConfig = match (true) {
                $st === 'disetujui' => [
                    'badge' => 'border-emerald-700/30 bg-emerald-700/10 text-emerald-700',
                    'dot' => 'bg-emerald-700',
                    'icon_bg' => 'bg-emerald-700/10 text-emerald-700 border-emerald-700/20',
                ],
                $st === 'ditolak' => [
                    'badge' => 'border-rose-700/30 bg-rose-700/10 text-rose-700',
                    'dot' => 'bg-rose-700',
                    'icon_bg' => 'bg-rose-700/10 text-rose-700 border-rose-700/20',
                ],
                $st === 'selesai' => [
                    'badge' => 'border-sky-700/30 bg-sky-700/10 text-sky-700',
                    'dot' => 'bg-sky-700',
                    'icon_bg' => 'bg-sky-700/10 text-sky-700 border-sky-700/20',
                ],
                default => [
                    'badge' => 'border-taupe/30 bg-taupe/10 text-taupe',
                    'dot' => 'bg-charcoal animate-pulse',
                    'icon_bg' => 'bg-taupe/10 text-taupe border-taupe/20',
                ],
            };
        @endphp

        <div class="mb-6 rounded-2xl border border-border bg-ivory p-6 shadow-xl animate-fade-up animate-duration-[700ms] animate-delay-75 animate-ease-out">
            <div class="flex items-center justify-between border-b border-border/80 pb-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl border {{ $statusConfig['icon_bg'] }}">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.158.79.418 1.071.228.246.526.402.858.448.283.04.57.017.842-.068.318-.098.6-.28.815-.523.25-.282.4-.653.4-1.057 0-.23-.035-.454-.1-.664M12 21a9 9 0 1 1 0-18 9 9 0 0 1 0 18Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-muted">Status Transaksi</p>
                        <p class="text-sm font-semibold text-charcoal">ID Peminjaman #{{ $peminjaman->id ?? $peminjaman->id_peminjaman }}</p>
                    </div>
                </div>

                <span class="inline-flex items-center gap-2 rounded-full border px-3.5 py-1.5 text-xs font-semibold tracking-wide uppercase {{ $statusConfig['badge'] }}">
                    <span class="h-2 w-2 rounded-full {{ $statusConfig['dot'] }}"></span>
                    {{ $peminjaman->status->value }}
                </span>
            </div>

            {{-- Specification Grid --}}
            <div class="mt-6 grid gap-6 sm:grid-cols-2">

                {{-- Peminjam --}}
                <div class="flex items-start gap-3.5">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-border bg-cream text-muted">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-muted">Peminjam</p>
                        <p class="mt-0.5 text-sm font-semibold text-charcoal">{{ $peminjaman->user?->nama ?? 'Tidak tersedia' }}</p>
                        @if (isset($peminjaman->user?->username))
                            <p class="text-xs text-muted">@ {{ $peminjaman->user->username }}</p>
                        @endif
                    </div>
                </div>

                {{-- Ruangan --}}
                <div class="flex items-start gap-3.5">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-border bg-cream text-muted">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.008v.008H6.75V6.75Zm0 3h.008v.008H6.75V9.75Zm0 3h.008v.008H6.75v-.008Zm0 3h.008v.008H6.75v-.008Zm6-9h.008v.008h-.008V6.75Zm0 3h.008v.008h-.008V9.75Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-muted">Ruangan</p>
                        <p class="mt-0.5 text-sm font-semibold text-charcoal">{{ $peminjaman->ruangan->nama_ruangan }}</p>
                    </div>
                </div>

                {{-- Tanggal --}}
                <div class="flex items-start gap-3.5">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-border bg-cream text-muted">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-muted">Tanggal Pelaksanaan</p>
                        <p class="mt-0.5 text-sm font-semibold text-charcoal">{{ $peminjaman->tanggal->format('d F Y') }}</p>
                    </div>
                </div>

                {{-- Waktu --}}
                <div class="flex items-start gap-3.5">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-border bg-cream text-muted">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-muted">Jam Operasional</p>
                        <div class="mt-1">
                            <span class="inline-flex items-center gap-1 font-mono text-xs font-semibold text-taupe rounded-lg border border-taupe/20 bg-taupe/10 px-2.5 py-1">
                                {{ substr($peminjaman->jam_mulai, 0, 5) }} – {{ substr($peminjaman->jam_selesai, 0, 5) }} WIB
                            </span>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Keperluan Section --}}
            <div class="mt-6 border-t border-border/80 pt-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-muted">Keperluan / Alasan Peminjaman</p>
                <div class="mt-2.5 rounded-xl border border-border/80 bg-cream p-4 text-sm leading-relaxed text-muted">
                    {{ $peminjaman->keperluan }}
                </div>
            </div>
        </div>

        {{-- Fasilitas Tambahan Card --}}
        <div class="rounded-2xl border border-border bg-ivory p-6 shadow-xl animate-fade-up animate-duration-[800ms] animate-delay-150 animate-ease-out">
            <div class="mb-4 flex items-center gap-2 border-b border-border/80 pb-3 text-xs font-semibold uppercase tracking-wider text-taupe">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                </svg>
                <span>Fasilitas Tambahan</span>
            </div>

            @if ($peminjaman->detailPeminjaman->isEmpty())
                <div class="rounded-xl border border-dashed border-border bg-cream px-4 py-6 text-center">
                    <p class="text-xs text-muted">Tidak ada fasilitas tambahan.</p>
                </div>
            @else
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($peminjaman->detailPeminjaman as $detail)
                        <div class="flex items-center justify-between rounded-xl border border-border bg-cream px-4 py-3 transition-colors hover:border-border">
                            <span class="text-sm font-medium text-charcoal">
                                {{ $detail->fasilitas->nama_fasilitas }}: {{ $detail->jumlah }}
                            </span>
                            <span class="inline-flex items-center rounded-lg border border-taupe/20 bg-taupe/10 px-2.5 py-1 font-mono text-xs font-semibold text-taupe">
                                {{ $detail->jumlah }} Unit
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Bottom Actions --}}
        <div class="mt-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between animate-fade-up animate-duration-[900ms] animate-delay-200 animate-ease-out">
            <a
                href="{{ route('admin.peminjaman.index') }}"
                class="inline-flex items-center gap-2 text-xs font-medium text-muted transition-colors hover:text-charcoal"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>Kembali ke Laporan</span>
            </a>

            <div class="flex flex-wrap gap-2">
                @if ($peminjaman->status->value === 'Menunggu')
                    <form method="POST" action="{{ route('admin.peminjaman.approve', $peminjaman) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-700 px-4 py-2 text-xs font-semibold text-white transition-colors hover:bg-emerald-800">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                            Setujui
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.peminjaman.reject', $peminjaman) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-rose-700/30 bg-rose-700/10 px-4 py-2 text-xs font-semibold text-rose-700 transition-colors hover:bg-rose-700/20">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m6 6 12 12" /></svg>
                            Tolak
                        </button>
                    </form>
                @endif

                <button
                    onclick="window.print()"
                    class="inline-flex items-center gap-2 rounded-xl border border-border bg-ivory px-4 py-2 text-xs font-medium text-muted transition-all hover:border-border hover:bg-cream hover:text-charcoal"
                >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231a1.125 1.125 0 0 1-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-19.126 0C1.033 7.441.265 8.375.265 9.456v6.294A2.25 2.25 0 0 0 2.515 18h1.092" />
                </svg>
                <span>Cetak Rincian</span>
            </button>
        </div>

    </div>
@endsection
