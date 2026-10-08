@extends('layouts.app')
@section('title', 'Edit Ruangan')
@section('content')
<div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
    <nav class="mb-6 flex items-center gap-2 text-xs font-medium text-muted">
        <a href="{{ route('dashboard') }}" class="hover:text-taupe">Dashboard</a>
        <span>/</span>
        <a href="{{ route('admin.ruangan.index') }}" class="hover:text-taupe">Kelola Ruangan</a>
        <span>/</span>
        <span class="text-charcoal">Edit Ruangan</span>
    </nav>
    <section class="glass-card rounded-[1.75rem] p-6 sm:p-8">
        <div class="mb-8">
            <span class="eyebrow"><span class="h-2 w-2 rounded-full bg-sky-700"></span>Perbarui data</span>
            <h1 class="section-title text-3xl sm:text-4xl">Edit Data Ruangan</h1>
            <p class="mt-3 text-sm leading-6 text-muted">Perbarui informasi dan status operasional untuk {{ $ruangan->nama_ruangan }}.</p>
        </div>
        <form method="POST" action="{{ route('admin.ruangan.update', $ruangan) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            <div>
                <label for="nama_ruangan" class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-muted">Nama Ruangan <span class="text-rose-700">*</span></label>
                <input id="nama_ruangan" name="nama_ruangan" type="text" value="{{ old('nama_ruangan', $ruangan->nama_ruangan) }}" maxlength="100" required class="w-full rounded-2xl border border-border bg-ivory px-4 py-3 text-sm text-charcoal placeholder-muted transition focus:border-taupe focus:outline-none focus:ring-2 focus:ring-taupe/20 @error('nama_ruangan') border-rose-700/50 focus:border-rose-700 focus:ring-rose-700/20 @enderror">
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
                <input id="kapasitas" name="kapasitas" type="number" value="{{ old('kapasitas', $ruangan->kapasitas) }}" min="1" required class="w-full rounded-2xl border border-border bg-ivory px-4 py-3 text-sm text-charcoal placeholder-muted transition focus:border-taupe focus:outline-none focus:ring-2 focus:ring-taupe/20 @error('kapasitas') border-rose-700/50 focus:border-rose-700 focus:ring-rose-700/20 @enderror">
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
                <input id="lokasi" name="lokasi" type="text" value="{{ old('lokasi', $ruangan->lokasi) }}" maxlength="150" required class="w-full rounded-2xl border border-border bg-ivory px-4 py-3 text-sm text-charcoal placeholder-muted transition focus:border-taupe focus:outline-none focus:ring-2 focus:ring-taupe/20 @error('lokasi') border-rose-700/50 focus:border-rose-700 focus:ring-rose-700/20 @enderror">
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
                <label for="gambar" class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-muted">Foto Thumbnail <span class="font-medium normal-case tracking-normal">(opsional)</span></label>
                @if ($ruangan->gambar_url)
                    <img src="{{ $ruangan->gambar_url }}" alt="Foto {{ $ruangan->nama_ruangan }}" class="mb-4 aspect-video w-full max-w-sm rounded-2xl border border-border object-cover">
                @endif
                <input id="gambar" name="gambar" type="file" accept="image/jpeg,image/png,image/webp" class="w-full rounded-2xl border border-border bg-ivory px-4 py-3 text-sm text-charcoal file:mr-4 file:rounded-lg file:border-0 file:bg-charcoal/5 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-charcoal hover:file:bg-charcoal/10 focus:border-taupe focus:outline-none focus:ring-2 focus:ring-taupe/20 @error('gambar') border-rose-700/50 focus:border-rose-700 focus:ring-rose-700/20 @enderror">
                <p class="mt-2 text-xs text-muted">JPG, PNG, atau WebP. Maksimal 10 MB.</p>
                @error('gambar')
                    <p class="mt-2 text-xs font-medium text-rose-700">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="status" class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-muted">Status Operasional <span class="text-rose-700">*</span></label>
                <select id="status" name="status" required class="w-full rounded-2xl border border-border bg-ivory px-4 py-3 text-sm text-charcoal transition focus:border-taupe focus:outline-none focus:ring-2 focus:ring-taupe/20 @error('status') border-rose-700/50 focus:border-rose-700 focus:ring-rose-700/20 @enderror">
                    @foreach ($statusOptions as $status)
                        <option value="{{ $status->value }}" @selected(old('status', $ruangan->status->value) === $status->value)> {{ $status->value }} </option>
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
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    Perbarui Ruangan
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
