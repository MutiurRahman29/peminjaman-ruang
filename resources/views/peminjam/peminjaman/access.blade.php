@extends('layouts.app')

@section('title', 'Cek Peminjaman')

@section('content')
    <div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
        <div class="grid items-center gap-8 lg:grid-cols-[0.9fr_1.1fr] lg:gap-14">
            <section class="max-w-xl" aria-labelledby="access-title">
                <p class="section-eyebrow">Akses pribadi</p>
                <h1 id="access-title" class="section-heading text-4xl sm:text-5xl">Cek peminjamanmu.</h1>
                <p class="section-copy">Gunakan nomor pengajuan dan kata sandi yang telah Anda buat untuk melihat status dan detail peminjaman.</p>

                <div class="mt-8 space-y-4">
                    <div class="flex gap-4 rounded-2xl border border-border bg-ivory p-4">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-cream text-taupe">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" /><path d="M8 8h8M8 12h8M8 16h5" /></svg>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-charcoal">Nomor pengajuan</p>
                            <p class="mt-1 text-xs leading-5 text-muted">Cari nomor yang tampil setelah membuat permohonan.</p>
                        </div>
                    </div>

                    <div class="flex gap-4 rounded-2xl border border-border bg-ivory p-4">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-cream text-taupe">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2" /><path d="M8 10V7a4 4 0 0 1 8 0v3" /></svg>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-charcoal">Kata sandi pribadi</p>
                            <p class="mt-1 text-xs leading-5 text-muted">Data ini hanya dapat diakses dengan credential yang benar.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="glass-card rounded-4xl p-5 sm:p-8" aria-labelledby="form-access-title">
                <div class="flex items-center justify-between gap-4 border-b border-border pb-5">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-taupe">Masuk akses</p>
                        <h2 id="form-access-title" class="mt-1 text-xl font-bold text-charcoal">Data peminjaman</h2>
                    </div>
                    <span class="rounded-full bg-cream px-3 py-1 text-[10px] font-bold uppercase tracking-[0.12em] text-muted">Lindungi</span>
                </div>

                <form method="POST" action="{{ route('peminjam.peminjaman.access') }}" class="mt-6 space-y-5">
                    @csrf

                    <div>
                        <label for="id_peminjaman" class="mb-2 block text-sm font-semibold text-charcoal">Nomor pengajuan <span class="text-rose-700">*</span></label>
                        <input id="id_peminjaman" name="id_peminjaman" type="number" min="1" inputmode="numeric" value="{{ old('id_peminjaman') }}" required placeholder="Contoh: 42" aria-invalid="{{ $errors->has('id_peminjaman') ? 'true' : 'false' }} aria-describedby="{{ $errors->has('id_peminjaman') ? 'id_peminjaman-error' : '' }}" class="w-full rounded-2xl border {{ $errors->has('id_peminjaman') ? 'border-rose-700 focus:border-rose-700 focus:ring-rose-700/20' : 'border-border focus:border-taupe focus:ring-taupe/20' }} bg-ivory px-4 py-3.5 text-sm text-charcoal transition focus:outline-none focus:ring-2">
                        @error('id_peminjaman')<p id="id_peminjaman-error" class="mt-2 text-xs font-medium text-rose-700">{{ $message }}</p>@enderror
                    </div>

                    <div x-data="{ passwordVisible: false }">
                        <label for="akses_password" class="mb-2 block text-sm font-semibold text-charcoal">Kata sandi <span class="text-rose-700">*</span></label>
                        <div class="relative">
                            <input id="akses_password" name="akses_password" :type="passwordVisible ? 'text' : 'password'" autocomplete="current-password" minlength="6" required placeholder="Kata sandi yang dibuat saat pengajuan" aria-invalid="{{ $errors->has('akses_password') ? 'true' : 'false' }} aria-describedby="{{ $errors->has('akses_password') ? 'akses_password-error' : '' }}" class="w-full rounded-2xl border {{ $errors->has('akses_password') ? 'border-rose-700 focus:border-rose-700 focus:ring-rose-700/20' : 'border-border focus:border-taupe focus:ring-taupe/20' }} bg-ivory px-4 py-3.5 pr-12 text-sm text-charcoal transition focus:outline-none focus:ring-2">
                            <button type="button" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-muted transition hover:text-taupe" :aria-label="passwordVisible ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'" @click="passwordVisible = !passwordVisible">
                                <svg x-show="!passwordVisible" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" /><circle cx="12" cy="12" r="2.5" /></svg>
                                <svg x-show="passwordVisible" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m3 3 18 18M10.6 6.2A10.7 10.7 0 0 1 12 6c6 0 9.5 6 9.5 6a15 15 0 0 1-2.2 2.8M6.2 6.2C3.8 8 2.5 12 2.5 12s3.5 6 9.5 6a9.7 9.7 0 0 0 3.4-.6M9.5 9.5a3.5 3.5 0 0 0 5 5" /></svg>
                            </button>
                        </div>
                        @error('akses_password')<p id="akses_password-error" class="mt-2 text-xs font-medium text-rose-700">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit" class="primary-button w-full">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12C2.5 6.7 6.7 2.5 12 2.5S21.5 6.7 21.5 12" /></svg>
                        Buka perkembangan
                    </button>
                </form>
            </section>
        </div>
    </div>
@endsection
