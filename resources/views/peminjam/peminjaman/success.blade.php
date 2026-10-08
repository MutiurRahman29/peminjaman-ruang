@extends('layouts.app')

@section('title', 'Pengajuan Berhasil Dikirim')

@section('content')
    <div class="mx-auto max-w-2xl px-4 py-16 sm:px-6 sm:py-20 lg:px-8">
        <section class="glass-card overflow-hidden rounded-4xl p-7 text-center sm:p-10" aria-labelledby="success-title">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-700/10 text-emerald-700">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>

            <p class="mt-6 text-xs font-bold uppercase tracking-[0.18em] text-taupe">Pengajuan diterima</p>
            <h1 id="success-title" class="mt-3 text-3xl font-bold tracking-tight text-charcoal sm:text-4xl">
                Permohonan kamu sudah dikirim
            </h1>
            <p class="mx-auto mt-4 max-w-xl text-sm leading-6 text-muted sm:text-base">
                Terima kasih. Pengajuan peminjaman ruangan sedang menunggu proses pemeriksaan oleh tim yang menanggung proses.
            </p>

            <div class="mt-8 rounded-2xl border border-border bg-cream p-5 text-left">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-muted">Nomor pengajuan</p>
                <p class="mt-2 text-2xl font-bold tracking-tight text-charcoal">#{{ $peminjaman->id_peminjaman }}</p>
                <p class="mt-2 text-sm text-muted">
                    Ruangan: <span class="font-semibold text-charcoal">{{ $peminjaman->ruangan->nama_ruangan }}</span>
                </p>
                <p class="text-sm text-muted">
                    Tanggal: <span class="font-semibold text-charcoal">{{ $peminjaman->tanggal->format('d F Y') }}</span>
                </p>
            </div>

            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ route('peminjam.peminjaman.access') }}" class="action-button-primary">Cek perkembangan</a>
                <a href="{{ route('home') }}" class="action-button-secondary">Kembali ke beranda</a>
                <a href="{{ route('peminjam.peminjaman.create') }}" class="action-button-secondary">Buat pengajuan lain</a>
            </div>
        </section>
    </div>
@endsection
