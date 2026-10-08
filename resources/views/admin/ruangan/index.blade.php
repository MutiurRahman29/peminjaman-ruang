@extends('layouts.app')

@section('title', 'Kelola Ruangan')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <nav class="mb-6 flex items-center gap-2 text-xs font-medium text-muted" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="transition-colors hover:text-taupe">Dashboard</a>
            <span>/</span>
            <span class="text-charcoal">Kelola Ruangan</span>
        </nav>

        <section class="mb-8 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <span class="eyebrow"><span class="h-2 w-2 rounded-full bg-emerald-700"></span>Master data</span>
                <h1 class="section-title">Kelola Ruangan</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-muted">Kelola kapasitas, lokasi, dan status operasional seluruh ruangan.</p>
            </div>
            <a href="{{ route('admin.ruangan.create') }}" class="primary-button">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Tambah Ruangan
            </a>
        </section>

        @if (session('success'))
            <div class="glass-card mb-6 flex items-start gap-3 rounded-2xl p-4 text-sm text-emerald-700"><svg class="h-5 w-5 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg><span>{{ session('success') }}</span></div>
        @endif
        @if (session('error'))
            <div class="glass-card mb-6 flex items-start gap-3 rounded-2xl border-rose-700/20 p-4 text-sm text-rose-700"><svg class="h-5 w-5 text-rose-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg><span>{{ session('error') }}</span></div>
        @endif

        <section class="glass-card overflow-hidden rounded-[1.75rem]">
            @if ($ruangan->isEmpty())
                <div class="flex flex-col items-center justify-center px-6 py-20 text-center">
                    <div class="mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-charcoal/10 text-taupe"><svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.008v.008H6.75V6.75zm0 3.75h.008v.008H6.75V10.5zm0 3.75h.008v.008H6.75v-.008zm0 3.75h.008v.008H6.75v-.008zm6-11.25h.008v.008h-.008V6.75zm0 3.75h.008v.008h-.008V10.5zm0 3.75h.008v.008h-.008v-.008zm0 3.75h.008v.008h-.008v-.008z" /></svg></div>
                    <h2 class="text-lg font-bold text-charcoal">Belum ada ruangan.</h2>
                    <p class="mt-2 max-w-sm text-sm text-muted">Tambahkan ruangan pertama untuk memulai pengelolaan.</p>
                    <a href="{{ route('admin.ruangan.create') }}" class="primary-button mt-6">Tambah Ruangan Baru</a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm" aria-label="Daftar Ruangan">
                        <thead class="border-b border-border bg-cream text-xs font-semibold uppercase tracking-[0.18em] text-muted"><tr><th class="px-6 py-4">Nama Ruangan</th><th class="px-6 py-4">Kapasitas</th><th class="px-6 py-4">Lokasi</th><th class="px-6 py-4">Status</th><th class="px-6 py-4 text-right">Aksi</th></tr></thead>
                        <tbody class="divide-y divide-border/30">
                            @foreach ($ruangan as $item)
                                <tr class="transition-colors hover:bg-cream">
                                    <td class="px-6 py-4 font-semibold text-charcoal">{{ $item->nama_ruangan }}</td>
                                    <td class="px-6 py-4 text-muted">{{ $item->kapasitas }} orang</td>
                                    <td class="px-6 py-4 text-muted">{{ $item->lokasi }}</td>
                                    <td class="px-6 py-4">@php $status = $item->status->value; @endphp <span class="inline-flex items-center gap-2 rounded-full border px-2.5 py-1 text-xs font-semibold {{ strtolower($status) === 'tersedia' ? 'border-emerald-700/20 bg-emerald-700/10 text-emerald-700' : 'border-taupe/20 bg-taupe/10 text-taupe' }}"><span class="h-1.5 w-1.5 rounded-full {{ strtolower($status) === 'tersedia' ? 'bg-emerald-700' : 'bg-charcoal' }}"></span>{{ $status }}</span></td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2"
                                            x-data="{ showConfirm: false }"
                                        >
                                            <a href="{{ route('admin.ruangan.edit', $item) }}" class="rounded-lg border border-border bg-ivory px-3 py-1.5 text-xs font-semibold text-charcoal transition-colors hover:border-taupe/40 hover:bg-taupe/10 hover:text-charcoal">Edit</a>
                                            <button
                                                type="button"
                                                x-on:click="showConfirm = true"
                                                class="rounded-lg border border-rose-700/30 bg-rose-700/10 px-3 py-1.5 text-xs font-semibold text-rose-700 transition-colors hover:bg-rose-700/20"
                                            >Hapus</button>

                                            {{-- Dialog Konfirmasi --}}
                                            <div
                                                x-show="showConfirm"
                                                x-transition:enter="transition ease-out duration-150"
                                                x-transition:enter-start="opacity-0"
                                                x-transition:enter-end="opacity-100"
                                                x-transition:leave="transition ease-in duration-100"
                                                x-transition:leave-start="opacity-100"
                                                x-transition:leave-end="opacity-0"
                                                class="fixed inset-0 z-50 flex items-center justify-center bg-charcoal/40 px-4 backdrop-blur-sm"
                                                x-on:keydown.escape.window="showConfirm = false"
                                            >
                                                <div
                                                    x-on:click.outside="showConfirm = false"
                                                    class="w-full max-w-sm rounded-2xl border border-border bg-ivory p-6 shadow-xl"
                                                >
                                                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-rose-700/10">
                                                        <svg class="h-6 w-6 text-rose-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                                                    </div>
                                                    <h2 class="text-base font-bold text-charcoal">Hapus Ruangan?</h2>
                                                    <p class="mt-1 text-sm text-muted">Ruangan <span class="font-semibold text-charcoal">{{ $item->nama_ruangan }}</span> akan dihapus secara permanen dan tidak dapat dikembalikan.</p>
                                                    <div class="mt-5 flex gap-3">
                                                        <button
                                                            type="button"
                                                            x-on:click="showConfirm = false"
                                                            class="action-button-secondary flex-1"
                                                        >Batal</button>
                                                        <form method="POST" action="{{ route('admin.ruangan.destroy', $item) }}" class="flex-1">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="w-full rounded-xl bg-rose-700 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-rose-800">Ya, Hapus</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <div class="mt-8"><a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-sm font-medium text-muted hover:text-taupe"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>Kembali ke Dashboard</a></div>
    </div>
@endsection
