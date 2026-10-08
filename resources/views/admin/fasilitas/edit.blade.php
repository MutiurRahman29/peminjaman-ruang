@extends('layouts.app')
@section('title', 'Edit Fasilitas')
@section('content')
<div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
    <nav class="mb-6 flex items-center gap-2 text-xs font-medium text-muted">
        <a href="{{ route('dashboard') }}" class="hover:text-taupe">Dashboard</a>
        <span>/</span>
        <a href="{{ route('admin.fasilitas.index') }}" class="hover:text-taupe">Kelola Fasilitas</a>
        <span>/</span>
        <span class="text-charcoal">Edit Fasilitas</span>
    </nav>
    <section class="glass-card rounded-[1.75rem] p-6 sm:p-8">
        <div class="mb-8">
            <span class="eyebrow"><span class="h-2 w-2 rounded-full bg-sky-700"></span>Perbarui data</span>
            <h1 class="section-title text-3xl sm:text-4xl">Edit Fasilitas</h1>
            <p class="mt-3 text-sm leading-6 text-muted">Perbarui detail inventaris untuk {{ $fasilitas->nama_fasilitas }}.</p>
        </div>
        <form method="POST" action="{{ route('admin.fasilitas.update', $fasilitas) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            <div>
                <label for="nama_fasilitas" class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-muted">Nama Fasilitas <span class="text-rose-700">*</span></label>
                <input id="nama_fasilitas" name="nama_fasilitas" type="text" value="{{ old('nama_fasilitas', $fasilitas->nama_fasilitas) }}" maxlength="100" required class="w-full rounded-2xl border border-border bg-ivory px-4 py-3 text-sm text-charcoal placeholder-muted transition focus:border-taupe focus:outline-none focus:ring-2 focus:ring-taupe/20 @error('nama_fasilitas') border-rose-700/50 focus:border-rose-700 focus:ring-rose-700/20 @enderror">
                @error('nama_fasilitas')
                    <div class="mt-2 flex items-center gap-1.5 text-xs text-rose-700">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>
            <div>
                <label for="jumlah" class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-muted">Jumlah Unit <span class="text-rose-700">*</span></label>
                <input id="jumlah" name="jumlah" type="number" value="{{ old('jumlah', $fasilitas->jumlah) }}" min="0" required class="w-full rounded-2xl border border-border bg-ivory px-4 py-3 text-sm text-charcoal placeholder-muted transition focus:border-taupe focus:outline-none focus:ring-2 focus:ring-taupe/20 @error('jumlah') border-rose-700/50 focus:border-rose-700 focus:ring-rose-700/20 @enderror">
                @error('jumlah')
                    <div class="mt-2 flex items-center gap-1.5 text-xs text-rose-700">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>
            <div>
                <label for="kondisi" class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-muted">Kondisi <span class="text-rose-700">*</span></label>
                <select id="kondisi" name="kondisi" required class="w-full rounded-2xl border border-border bg-ivory px-4 py-3 text-sm text-charcoal transition focus:border-taupe focus:outline-none focus:ring-2 focus:ring-taupe/20 @error('kondisi') border-rose-700/50 focus:border-rose-700 focus:ring-rose-700/20 @enderror">
                    @foreach ($kondisiOptions as $kondisi)
                        <option value="{{ $kondisi->value }}" @selected(old('kondisi', $fasilitas->kondisi->value) === $kondisi->value)> {{ $kondisi->value }} </option>
                    @endforeach
                </select>
                @error('kondisi')
                    <div class="mt-2 flex items-center gap-1.5 text-xs text-rose-700">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>
            <div>
                <label for="keterangan" class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-muted">Keterangan <span class="text-xs font-normal text-muted">(Opsional)</span></label>
                <textarea id="keterangan" name="keterangan" maxlength="1000" rows="3" class="w-full rounded-2xl border border-border bg-ivory p-4 text-sm text-charcoal placeholder-muted transition focus:border-taupe focus:outline-none focus:ring-2 focus:ring-taupe/20 @error('keterangan') border-rose-700/50 focus:border-rose-700 focus:ring-rose-700/20 @enderror">{{ old('keterangan', $fasilitas->keterangan) }}</textarea>
                @error('keterangan')
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
                @if ($fasilitas->gambar_url)
                    <img src="{{ $fasilitas->gambar_url }}" alt="Foto {{ $fasilitas->nama_fasilitas }}" class="mb-4 aspect-video w-full max-w-sm rounded-2xl border border-border object-cover">
                @endif
                <input id="gambar" name="gambar" type="file" accept="image/jpeg,image/png,image/webp" class="w-full rounded-2xl border border-border bg-ivory px-4 py-3 text-sm text-charcoal file:mr-4 file:rounded-lg file:border-0 file:bg-charcoal/5 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-charcoal hover:file:bg-charcoal/10 focus:border-taupe focus:outline-none focus:ring-2 focus:ring-taupe/20 @error('gambar') border-rose-700/50 focus:border-rose-700 focus:ring-rose-700/20 @enderror">
                <p class="mt-2 text-xs text-muted">JPG, PNG, atau WebP. Maksimal 10 MB.</p>
                @error('gambar')
                    <p class="mt-2 text-xs font-medium text-rose-700">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex flex-col-reverse gap-3 border-t border-border pt-6 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.fasilitas.index') }}" class="soft-button">Batal</a>
                <button type="submit" class="primary-button">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    Perbarui Data
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
