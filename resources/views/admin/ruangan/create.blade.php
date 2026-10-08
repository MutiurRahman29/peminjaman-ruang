@extends('layouts.app')
@section('title', 'Tambah Ruangan')
@section('content')
<div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
    <nav class="mb-6 flex items-center gap-2 text-xs font-medium text-muted">
        <a href="{{ route('dashboard') }}" class="hover:text-taupe">Dashboard</a>
        <span>/</span>
        <a href="{{ route('admin.ruangan.index') }}" class="hover:text-taupe">Kelola Ruangan</a>
        <span>/</span>
        <span class="text-charcoal">Tambah Ruangan</span>
    </nav>
    <section class="glass-card rounded-[1.75rem] p-6 sm:p-8">
        <div class="mb-8">
            <span class="eyebrow"><span class="h-2 w-2 rounded-full bg-charcoal"></span>Data baru</span>
            <h1 class="section-title text-3xl sm:text-4xl">Tambah Ruangan Baru</h1>
            <p class="mt-3 text-sm leading-6 text-muted">Lengkapi detail ruangan sebelum data dapat digunakan untuk peminjaman.</p>
        </div>
        <form method="POST" action="{{ route('admin.ruangan.store') }}" class="space-y-6">
            @csrf
            <div>
                <label for="nama_ruangan" class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-muted">Nama Ruangan <span class="text-rose-700">*</span></label>
                <input id="nama_ruangan" name="nama_ruangan" type="text" value="{{ old('nama_ruangan') }}" maxlength="100" required placeholder="Contoh: Aula Utama" class="w-full rounded-2xl border border-border bg-ivory px-4 py-3 text-sm text-charcoal placeholder-muted transition focus:border-taupe focus:outline-none focus:ring-2 focus:ring-taupe/20 @error('nama_ruangan') border-rose-700/50 focus:border-rose-700 focus:ring-rose-700/20 @enderror">
                @error('nama_ruangan')
                    <div class="mt-2 flex items-center gap-1.5 text-xs text-rose-700">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>
            <div>
                <label for="kapasitas" class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-muted">Kapasitas <span class="text-rose-700">*</span></label>
                <input id="kapasitas" name="kapasitas" type="number" value="{{ old('kapasitas') }}" min="1" required placeholder="Contoh: 50" class="w-full rounded-2xl border border-border bg-ivory px-4 py-3 text-sm text-charcoal placeholder-muted transition focus:border-taupe focus:outline-none focus:ring-2 focus:ring-taupe/20 @error('kapasitas') border-rose-700/50 focus:border-rose-700 focus:ring-rose-700/20 @enderror">
                @error('kapasitas')
                    <div class="mt-2 flex items-center gap-1.5 text-xs text-rose-700">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>
            <div>
                <label for="lokasi" class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-muted">Lokasi / Gedung <span class="text-rose-700">*</span></label>
                <input id="lokasi" name="lokasi" type="text" value="{{ old('lokasi') }}" maxlength="150" required placeholder="Contoh: Gedung A Lantai 2" class="w-full rounded-2xl border border-border bg-ivory px-4 py-3 text-sm text-charcoal placeholder-muted transition focus:border-taupe focus:outline-none focus:ring-2 focus:ring-taupe/20 @error('lokasi') border-rose-700/50 focus:border-rose-700 focus:ring-rose-700/20 @enderror">
                @error('lokasi')
                    <div class="mt-2 flex items-center gap-1.5 text-xs text-rose-700">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>
            <div>
                <label for="status" class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-muted">Status Operasional <span class="text-rose-700">*</span></label>
                <select id="status" name="status" required class="w-full rounded-2xl border border-border bg-ivory px-4 py-3 text-sm text-charcoal transition focus:border-taupe focus:outline-none focus:ring-2 focus:ring-taupe/20 @error('status') border-rose-700/50 focus:border-rose-700 focus:ring-rose-700/20 @enderror">
                    <option value="" disabled @selected(!old('status'))>Pilih status ruangan</option>
                    @foreach ($statusOptions as $status)
                        <option value="{{ $status->value }}" @selected(old('status') === $status->value)> {{ $status->value }} </option>
                    @endforeach
                </select>
                @error('status')
                    <div class="mt-2 flex items-center gap-1.5 text-xs text-rose-700">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>
            <div class="flex flex-col-reverse gap-3 border-t border-border pt-6 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.ruangan.index') }}" class="soft-button">Batal</a>
                <button type="submit" class="primary-button">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Simpan Ruangan
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
