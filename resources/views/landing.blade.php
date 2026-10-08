@extends('layouts.app')

@section('title', 'Sistem Pengelolaan Ruang dan Fasilitas')

@section('content')
    <section id="hero" class="relative min-h-[100vh] min-h-[100dvh] overflow-hidden border-b border-border bg-[#2f2a27]">
        <img
            src="https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1600&q=80"
            alt="Interior ruang kerja yang modern"
            class="absolute inset-0 h-full w-full object-cover object-center"
            loading="eager"
            fetchpriority="high"
        >
        <div class="absolute inset-0 bg-gradient-to-r from-[#2f2a27]/90 via-[#2f2a27]/70 to-[#2f2a27]/35"></div>
        <div class="absolute -left-24 top-20 h-72 w-72 rounded-full bg-[#d8cab8]/15 blur-3xl"></div>
        <div class="absolute -right-16 bottom-10 h-80 w-80 rounded-full bg-[#b8c9bd]/10 blur-3xl"></div>

        <div class="page-section relative grid min-h-[100svh] items-center gap-8 py-4 sm:gap-10 sm:py-6 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)] lg:py-8">
            <div class="relative max-w-2xl text-left">
                <h1 class="max-w-[48rem] text-[clamp(2rem,5vw,3.5rem)] font-extrabold leading-[1.02] tracking-[-0.045em] text-white xl:text-[4rem]">
                    <span class="block">Satu ruang untuk</span>
                    <span class="mt-1 block max-w-[48rem] text-[#d8cab8] lg:whitespace-nowrap sm:mt-2">memenuhi kebutuhan Anda.</span>
                </h1>

                <p class="mt-5 max-w-xl text-[0.95rem] leading-7 text-[#f0ece7]/80 sm:text-base">
                    Cek ketersediaan ruang, siapkan fasilitas, dan ajukan peminjaman dengan proses yang jelas, cepat, dan tanpa kebingungan.
                </p>

                <div class="mt-8 flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:flex-wrap">
                    <a href="{{ route('peminjam.ruangan.index') }}" class="action-button-primary w-full shadow-[0_10px_24px_rgba(0,0,0,0.2)] sm:w-auto">Jelajahi katalog <span aria-hidden="true">→</span></a>
                    <a href="{{ route('peminjam.peminjaman.create') }}" class="action-button-secondary w-full border-white/20 bg-white/10 text-white hover:bg-white/15 hover:text-white sm:w-auto">Ajukan peminjaman</a>
                </div>
        </div>
    </section>

    <section id="tentang" class="min-h-[100vh] min-h-[100dvh] border-b border-border bg-[#f8f5f1] scroll-mt-28 py-20 sm:py-24">
        <div class="page-section flex min-h-[100vh] min-h-[100dvh] items-center">
            <div class="w-full">
                <div class="grid gap-8 lg:grid-cols-[0.8fr_1.2fr] lg:items-end">
                    <div>
                        <p class="section-eyebrow">Tentang sistem</p>
                        <h2 class="section-heading max-w-xl">Platform yang membantu setiap langkah terasa lebih tenang.</h2>
                    </div>
                    <p class="max-w-2xl text-base leading-7 text-[#5d5853] sm:text-lg">
                        Informasi ruang, fasilitas, dan status peminjaman tersusun dalam satu pengalaman yang mudah dibaca. Semuanya dirancang agar pengguna dapat menemukan kebutuhan dan melanjutkan proses dengan lebih percaya diri.
                    </p>
                </div>

                <div class="mt-12 grid gap-5 lg:grid-cols-3">
                    <article class="group rounded-3xl border border-[#e0d9d1] bg-white p-6 shadow-[0_10px_24px_rgba(41,39,35,0.04)] transition duration-300 hover:-translate-y-1 hover:border-[#cab8ab] hover:shadow-[0_16px_32px_rgba(41,39,35,0.08)]">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#725b4d]">01</p>
                        <h3 class="mt-4 text-xl font-bold text-[#292723]">Informasi yang mudah dibaca</h3>
                        <p class="mt-3 text-sm leading-6 text-[#5d5853]">Setiap detail tersedia dengan cara yang ringkas, jelas, dan mudah dipahami.</p>
                    </article>
                    <article class="group rounded-3xl border border-[#e0d9d1] bg-white p-6 shadow-[0_10px_24px_rgba(41,39,35,0.04)] transition duration-300 hover:-translate-y-1 hover:border-[#cab8ab] hover:shadow-[0_16px_32px_rgba(41,39,35,0.08)]">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#725b4d]">02</p>
                        <h3 class="mt-4 text-xl font-bold text-[#292723]">Alur yang lebih terarah</h3>
                        <p class="mt-3 text-sm leading-6 text-[#5d5853]">Mulai dari pemilihan kebutuhan hingga proses persetujuan memiliki arah yang konsisten.</p>
                    </article>
                    <article class="group rounded-3xl border border-[#e0d9d1] bg-white p-6 shadow-[0_10px_24px_rgba(41,39,35,0.04)] transition duration-300 hover:-translate-y-1 hover:border-[#cab8ab] hover:shadow-[0_16px_32px_rgba(41,39,35,0.08)]">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#725b4d]">03</p>
                        <h3 class="mt-4 text-xl font-bold text-[#292723]">Peran yang tetap fokus</h3>
                        <p class="mt-3 text-sm leading-6 text-[#5d5853]">Peminjam, petugas, dan admin bisa bekerja dengan kebutuhan dan akses yang tepat.</p>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <section id="fitur" class="min-h-[100vh] min-h-[100dvh] bg-[#2d2926] scroll-mt-28 py-20 sm:py-24">
        <div class="page-section flex min-h-[100vh] min-h-[100dvh] items-center">
            <div class="w-full">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-[#f7f5f0]/60">Fitur utama</p>
                        <h2 class="mt-3 max-w-2xl text-[1.75rem] font-bold tracking-[-0.03em] text-[#f7f5f0] sm:text-2xl lg:text-[2.5rem]">Mulai dari kebutuhan, lanjut dengan langkah yang tepat.</h2>
                    </div>
                </div>

                <div class="mt-12 grid gap-5 lg:grid-cols-3">
                    <article class="group overflow-hidden rounded-[1.75rem] border border-[#47413d] bg-[#f5f0e9] transition duration-300 hover:-translate-y-1 hover:border-[#725b4d]/60">
                        <img src="https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1200&q=80" alt="Interior ruangan kerja modern" class="block aspect-[16/10] w-full object-cover" width="720" height="440" loading="lazy">
                        <div class="p-6 sm:p-7">
                            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-[#725b4d]">Katalog ruang</p>
                            <h3 class="mt-4 text-[1.35rem] font-bold text-[#292723]">Temukan ruang yang sesuai</h3>
                            <p class="mt-3 text-sm leading-6 text-[#54504a]">Lihat kapasitas, lokasi, dan status operasional sebelum memilih kebutuhan.</p>
                            <a href="{{ route('peminjam.ruangan.index') }}" class="mt-6 inline-flex text-sm font-semibold text-[#292723]">Lihat ruangan <span class="ml-2" aria-hidden="true">→</span></a>
                        </div>
                    </article>

                    <article class="group overflow-hidden rounded-[1.75rem] border border-[#47413d] bg-[#f5f0e9] transition duration-300 hover:-translate-y-1 hover:border-[#725b4d]/60">
                        <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=1200&q=80" alt="Tim sedang berdiskusi di ruang rapat" class="block aspect-[16/10] w-full object-cover" width="720" height="440" loading="lazy">
                        <div class="p-6 sm:p-7">
                            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-[#725b4d]">Inventaris fasilitas</p>
                            <h3 class="mt-4 text-[1.35rem] font-bold text-[#292723]">Periksa kebutuhan dengan tepat</h3>
                            <p class="mt-3 text-sm leading-6 text-[#54504a]">Pilih fasilitas berdasarkan kondisi, jumlah, dan keterangan yang tersedia.</p>
                            <a href="{{ route('peminjam.fasilitas.index') }}" class="mt-6 inline-flex text-sm font-semibold text-[#292723]">Lihat fasilitas <span class="ml-2" aria-hidden="true">→</span></a>
                        </div>
                    </article>

                    <article class="group flex flex-col overflow-hidden rounded-[1.75rem] border border-[#47413d] bg-[#f5f0e9] transition duration-300 hover:-translate-y-1 hover:border-[#725b4d]/60">
                        <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1200&q=80" alt="Pengguna mengisi formulir peminjaman" class="block aspect-[16/10] w-full object-cover" width="720" height="440" loading="lazy">
                        <div class="flex flex-1 flex-col p-6 sm:p-7">
                            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-[#725b4d]">Pengajuan</p>
                            <h3 class="mt-4 text-[1.35rem] font-bold text-[#292723]">Tinjau dan kirim permohonan</h3>
                            <p class="mt-3 text-sm leading-6 text-[#54504a]">Selesaikan proses dengan status yang mudah dipantau oleh peminjam dan petugas.</p>
                            <a href="{{ route('peminjam.peminjaman.create') }}" class="mt-auto inline-flex pt-6 text-sm font-semibold text-[#292723]">Buat peminjaman <span class="ml-2" aria-hidden="true">→</span></a>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <section id="cara-kerja" class="min-h-[100vh] min-h-[100dvh] border-y border-border bg-[#f5f1eb] scroll-mt-28 py-20 sm:py-24">
        <div class="page-section flex min-h-[100vh] min-h-[100dvh] items-center">
            <div class="w-full">
                <div class="max-w-3xl">
                    <p class="section-eyebrow">Bagaimana cara kerjanya</p>
                    <h2 class="text-3xl font-bold tracking-[-0.03em] text-[#292723] sm:text-4xl">Dari kebutuhan sederhana menjadi peminjaman yang terencana.</h2>
                    <p class="mt-5 max-w-2xl text-base leading-7 text-[#5d5853] sm:text-lg">Ikuti alur yang jelas mulai dari memilih ruang hingga memantau hasil persetujuan. Setiap tahap dapat dilakukan melalui halaman yang terpisah dan mudah dipahami.</p>
                </div>

                <div class="mt-12 grid gap-5 md:grid-cols-2 lg:grid-cols-4">
                    <article class="rounded-[1.5rem] border border-[#dcd6cc] bg-white p-6 shadow-[0_10px_24px_rgba(41,39,35,0.04)]">
                        <span class="flex h-11 w-11 items-center justify-center rounded-full bg-[#292723] text-sm font-bold text-[#f7f5f0]">1</span>
                        <h3 class="mt-7 text-xl font-bold text-[#292723]">Pilih ruang atau fasilitas</h3>
                        <p class="mt-3 text-sm leading-6 text-[#5d5853]">Lihat katalog ruang dan inventaris fasilitas untuk menemukan kebutuhan yang sesuai.</p>
                        <a href="{{ route('peminjam.ruangan.index') }}" class="mt-6 inline-flex text-sm font-semibold text-[#725b4d]">Lihat katalog ruang <span class="ml-2" aria-hidden="true">→</span></a>
                    </article>

                    <article class="rounded-[1.5rem] border border-[#dcd6cc] bg-white p-6 shadow-[0_10px_24px_rgba(41,39,35,0.04)]">
                        <span class="flex h-11 w-11 items-center justify-center rounded-full bg-[#725b4d] text-sm font-bold text-white">2</span>
                        <h3 class="mt-7 text-xl font-bold text-[#292723]">Cek ketersediaan</h3>
                        <p class="mt-3 text-sm leading-6 text-[#5d5853]">Periksa tanggal, jam, dan status ruang untuk memastikan jadwal dapat digunakan.</p>
                        <a href="{{ route('peminjam.ruangan.index') }}" class="mt-6 inline-flex text-sm font-semibold text-[#725b4d]">Cek ketersediaan <span class="ml-2" aria-hidden="true">→</span></a>
                    </article>

                    <article class="rounded-[1.5rem] border border-[#dcd6cc] bg-white p-6 shadow-[0_10px_24px_rgba(41,39,35,0.04)]">
                        <span class="flex h-11 w-11 items-center justify-center rounded-full bg-[#5a7a65] text-sm font-bold text-white">3</span>
                        <h3 class="mt-7 text-xl font-bold text-[#292723]">Isi dan submit permohonan</h3>
                        <p class="mt-3 text-sm leading-6 text-[#5d5853]">Pilih ruang, tentukan jadwal, masukkan data peminjam, dan kirim permohonan untuk persetujuan.</p>
                        <a href="{{ route('peminjam.peminjaman.create') }}" class="mt-6 inline-flex text-sm font-semibold text-[#725b4d]">Buat peminjaman <span class="ml-2" aria-hidden="true">→</span></a>
                    </article>

                    <article class="rounded-[1.5rem] border border-[#dcd6cc] bg-white p-6 shadow-[0_10px_24px_rgba(41,39,35,0.04)]">
                        <span class="flex h-11 w-11 items-center justify-center rounded-full bg-[#8b5e52] text-sm font-bold text-white">4</span>
                        <h3 class="mt-7 text-xl font-bold text-[#292723]">Pantau status dan hasil</h3>
                        <p class="mt-3 text-sm leading-6 text-[#5d5853]">Gunakan nomor peminjaman dan kata sandi untuk melihat progres, status, dan detail permohonan.</p>
                        <a href="{{ route('peminjam.peminjaman.access') }}" class="mt-6 inline-flex text-sm font-semibold text-[#725b4d]">Cek peminjaman <span class="ml-2" aria-hidden="true">→</span></a>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <section id="siap-mulai" class="relative min-h-[100vh] min-h-[100dvh] overflow-hidden bg-[#2b2825] scroll-mt-28 py-20 sm:py-24">
        <div class="absolute -left-20 top-20 h-80 w-80 rounded-full bg-[#725b4d]/12 blur-3xl"></div>
        <div class="absolute -right-16 bottom-16 h-72 w-72 rounded-full bg-[#5a7a65]/12 blur-3xl"></div>
        <div class="page-section relative flex min-h-[100vh] min-h-[100dvh] items-center justify-center text-center">
            <div class="w-full">
                <p class="text-center text-[10px] font-bold uppercase tracking-[0.18em] text-[#f7f5f0]/60">Siap mulai</p>
                <h2 class="mx-auto mt-4 max-w-3xl text-3xl font-bold tracking-[-0.03em] text-[#f7f5f0] sm:text-4xl lg:text-5xl">Tentukan langkah berikutnya dan mulailah sekarang.</h2>
                <p class="mx-auto mt-5 max-w-2xl text-base leading-7 text-[#f7f5f0]/65 sm:text-lg">Setiap kebutuhan yang kamu susun adalah awal dari proses yang lebih terencana. Ayo lanjutkan—langkah kecil hari ini bisa membuka kesempatan besar ke depan.</p>
                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('peminjam.ruangan.index') }}" class="action-button-primary shadow-[0_12px_28px_rgba(0,0,0,0.18)]">Lihat katalog <span aria-hidden="true">→</span></a>
                </div>
            </div>
        </div>
    </section>
@endsection
