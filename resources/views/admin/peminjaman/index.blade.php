@extends('layouts.app')

@section('title', 'Laporan Peminjaman')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <nav class="mb-6 flex items-center gap-2 text-xs font-medium text-muted" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="transition-colors hover:text-taupe">Dashboard</a>
            <span>/</span>
            <span class="text-charcoal">Laporan Peminjaman</span>
        </nav>

        <section class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <span class="eyebrow"><span class="h-2 w-2 rounded-full bg-charcoal"></span>Aktivitas operasional</span>
                <h1 class="section-title">Laporan Peminjaman</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-muted">Pantau dan analisis seluruh aktivitas peminjaman ruangan dalam sistem.</p>
            </div>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 self-start rounded-xl border border-border bg-ivory px-4 py-2.5 text-xs font-semibold text-muted transition-all hover:border-taupe/40 hover:bg-cream hover:text-charcoal sm:self-auto">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                Kembali ke Dashboard
            </a>
        </section>

        {{-- Summary Cards Grid --}}
        <div
            class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-4 animate-fade-up animate-duration-[700ms] animate-delay-75 animate-ease-out">
            @foreach ($ringkasan as $status => $total)
                @php
                    $statusLower = strtolower($status);
                    $dotColor = match (true) {
                        str_contains($statusLower, 'setuju') || str_contains($statusLower, 'disetujui')
                            => 'bg-emerald-400 shadow-emerald-500/50',
                        str_contains($statusLower, 'tolak') || str_contains($statusLower, 'ditolak')
                            => 'bg-rose-400 shadow-rose-500/50',
                        str_contains($statusLower, 'selesai') => 'bg-sky-400 shadow-sky-500/50',
                        default => 'bg-amber-400 shadow-amber-500/50',
                    };
                    $badgeStyle = match (true) {
                        str_contains($statusLower, 'setuju') || str_contains($statusLower, 'disetujui')
                            => 'text-emerald-700 border-emerald-700/20 bg-emerald-700/10',
                        str_contains($statusLower, 'tolak') || str_contains($statusLower, 'ditolak')
                            => 'text-rose-700 border-rose-700/20 bg-rose-700/10',
                        str_contains($statusLower, 'selesai') => 'text-sky-700 border-sky-700/20 bg-sky-700/10',
                        default => 'text-taupe border-taupe/20 bg-taupe/10',
                    };
                @endphp
                <div class="glass-card rounded-2xl p-5 shadow-sm transition-all hover:-translate-y-0.5 hover:border-taupe/30">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-2 rounded-lg border px-2.5 py-1 text-xs font-semibold tracking-wider uppercase {{ $badgeStyle }}">
                            <span class="h-2 w-2 rounded-full {{ $dotColor }} shadow-sm"></span>
                            {{ $status }}
                        </span>
                    </div>
                    <div class="mt-4">
                        <p class="text-3xl font-bold tracking-tight text-charcoal">{{ number_format($total) }}</p>
                        <p class="mt-1 text-xs text-muted">Total Transaksi</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Filter Card --}}
        <section class="glass-card mb-8 rounded-2xl p-6 animate-fade-up animate-duration-[800ms] animate-delay-100 animate-ease-out">
            <div class="mb-5 flex items-center gap-2 border-b border-border pb-3 text-xs font-semibold uppercase tracking-wider text-taupe">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                </svg>
                <span>Filter Laporan</span>
            </div>

            <form method="GET" action="{{ route('admin.peminjaman.index') }}"
                class="grid gap-4 sm:grid-cols-2 lg:grid-cols-6 items-end">

                {{-- Status --}}
                <div class="lg:col-span-1">
                    <label for="status"
                        class="mb-2 block text-xs font-semibold uppercase tracking-wider text-muted">Status</label>
                    <div class="relative">
                        <select id="status" name="status"
                            class="w-full appearance-none rounded-xl border border-border bg-ivory px-3.5 py-2.5 pr-8 text-sm text-charcoal transition-all focus:border-taupe focus:bg-cream focus:outline-none focus:ring-2 focus:ring-taupe/20">
                            <option value="">Semua status</option>
                            @foreach (\App\Enums\StatusPeminjaman::cases() as $status)
                                <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>
                                    {{ $status->value }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-muted">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </div>
                    </div>
                    @error('status')
                        <p class="mt-1 text-xs text-rose-700">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Ruangan --}}
                <div class="lg:col-span-1">
                    <label for="id_ruangan"
                        class="mb-2 block text-xs font-semibold uppercase tracking-wider text-muted">Ruangan</label>
                    <div class="relative">
                        <select id="id_ruangan" name="id_ruangan"
                            class="w-full appearance-none rounded-xl border border-border bg-ivory px-3.5 py-2.5 pr-8 text-sm text-charcoal transition-all focus:border-taupe focus:bg-cream focus:outline-none focus:ring-2 focus:ring-taupe/20">
                            <option value="">Semua ruangan</option>
                            @foreach ($ruangan as $item)
                                <option value="{{ $item->id_ruangan }}" @selected((string) ($filters['id_ruangan'] ?? '') === (string) $item->id_ruangan)>
                                    {{ $item->nama_ruangan }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-muted">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </div>
                    </div>
                    @error('id_ruangan')
                        <p class="mt-1 text-xs text-rose-700">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Peminjam --}}
                <div class="lg:col-span-1">
                    <label for="id_user"
                        class="mb-2 block text-xs font-semibold uppercase tracking-wider text-muted">Peminjam</label>
                    <div class="relative">
                        <select id="id_user" name="id_user"
                            class="w-full appearance-none rounded-xl border border-border bg-ivory px-3.5 py-2.5 pr-8 text-sm text-charcoal transition-all focus:border-taupe focus:bg-cream focus:outline-none focus:ring-2 focus:ring-taupe/20">
                            <option value="">Semua peminjam</option>
                            @foreach ($users as $item)
                                <option value="{{ $item->id_user }}" @selected((string) ($filters['id_user'] ?? '') === (string) $item->id_user)>
                                    {{ $item->nama }} ({{ $item->username }})
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2.5 text-muted">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </div>
                    </div>
                    @error('id_user')
                        <p class="mt-1 text-xs text-rose-700">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tanggal Mulai --}}
                <div class="lg:col-span-1">
                    <label for="tanggal_mulai"
                        class="mb-2 block text-xs font-semibold uppercase tracking-wider text-muted">Tgl Mulai</label>
                    <input id="tanggal_mulai" name="tanggal_mulai" type="date"
                        value="{{ $filters['tanggal_mulai'] ?? '' }}"
                        class="w-full rounded-xl border border-border bg-ivory px-3.5 py-2.5 text-sm text-charcoal transition-all focus:border-taupe focus:bg-cream focus:outline-none focus:ring-2 focus:ring-taupe/20">
                    @error('tanggal_mulai')
                        <p class="mt-1 text-xs text-rose-700">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tanggal Selesai --}}
                <div class="lg:col-span-1">
                    <label for="tanggal_selesai"
                        class="mb-2 block text-xs font-semibold uppercase tracking-wider text-muted">Tgl Selesai</label>
                    <input id="tanggal_selesai" name="tanggal_selesai" type="date"
                        value="{{ $filters['tanggal_selesai'] ?? '' }}"
                        class="w-full rounded-xl border border-border bg-ivory px-3.5 py-2.5 text-sm text-charcoal transition-all focus:border-taupe focus:bg-cream focus:outline-none focus:ring-2 focus:ring-taupe/20">
                    @error('tanggal_selesai')
                        <p class="mt-1 text-xs text-rose-700">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-2 sm:col-span-2 lg:col-span-1">
                    <button type="submit" class="primary-button w-full">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                        Filter
                    </button>
                    <a href="{{ route('admin.peminjaman.index') }}" title="Reset Filter" class="action-button-secondary rounded-xl px-3.5 py-2.5">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                    </a>
                </div>
            </form>
        </section>

        @if (session('error'))
            <div class="mb-6 border border-rose-700/20 bg-rose-700/10 p-4 text-sm text-rose-700" role="alert">
                {{ session('error') }}
            </div>
        @endif

        {{-- Main Table Container --}}
        <section class="glass-card overflow-hidden rounded-[1.75rem]">
            @if ($peminjaman->isEmpty())
                <div class="flex flex-col items-center justify-center px-6 py-20 text-center">
                    <div class="mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-charcoal/10 text-taupe">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m5.25 11.25h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm-3-6h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm-3-6h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" /></svg>
                    </div>
                    <h2 class="text-lg font-bold text-charcoal">Tidak Ada Data Peminjaman</h2>
                    <p class="mt-2 max-w-sm text-sm text-muted">Tidak ditemukan data transaksi yang sesuai dengan kriteria filter yang Anda terapkan.</p>
                    @if (!empty(filter_var_array($filters)))
                        <a href="{{ route('admin.peminjaman.index') }}" class="primary-button mt-6">Reset Filter Search</a>
                    @endif
                </div>
            @else
                {{-- Table Data --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm" aria-label="Daftar Peminjaman">
                        <thead class="border-b border-border bg-cream text-xs font-semibold uppercase tracking-[0.18em] text-muted">
                            <tr>
                                <th class="px-6 py-4">Peminjam</th>
                                <th class="px-6 py-4">Email</th>
                                <th class="px-6 py-4">WhatsApp</th>
                                <th class="px-6 py-4">Ruangan</th>
                                <th class="px-6 py-4">Tanggal</th>
                                <th class="px-6 py-4">Waktu</th>
                                <th class="px-6 py-4">Keperluan</th>
                                <th class="px-6 py-4">Fasilitas</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/30">
                            @foreach ($peminjaman as $item)
                                <tr class="transition-colors hover:bg-cream">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-charcoal">{{ $item->user?->nama ?? $item->nama_pemohon }}</div>
                                        <div class="mt-0.5 text-xs text-muted">{{ $item->user?->username ?? 'Tidak tersedia' }}</div>
                                    </td>
                                    <td class="px-6 py-4 break-all text-xs text-muted">{{ $item->email_pemohon }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-muted">{{ $item->whatsapp_pemohon }}</td>
                                    <td class="px-6 py-4 font-medium text-charcoal">{{ $item->ruangan?->nama_ruangan ?? 'Tidak tersedia' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-muted">{{ $item->tanggal->format('d M Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 rounded-lg border border-border bg-cream px-2.5 py-1 font-mono text-xs font-semibold text-taupe">{{ substr($item->jam_mulai, 0, 5) }} – {{ substr($item->jam_selesai, 0, 5) }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-muted" title="{{ $item->keperluan }}">{{ $item->keperluan }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-md bg-charcoal/10 px-2 py-1 text-xs font-semibold text-charcoal">{{ $item->detailPeminjaman->count() }} Jenis</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $status = strtolower($item->status->value);
                                            $statusClass = match (true) {
                                                $status === 'disetujui' => 'border-emerald-700/20 bg-emerald-700/10 text-emerald-700',
                                                $status === 'ditolak' => 'border-rose-700/20 bg-rose-700/10 text-rose-700',
                                                $status === 'selesai' => 'border-sky-700/20 bg-sky-700/10 text-sky-700',
                                                default => 'border-taupe/20 bg-taupe/10 text-taupe',
                                            };
                                            $statusDotClass = match (true) {
                                                $status === 'disetujui' => 'bg-emerald-700',
                                                $status === 'ditolak' => 'bg-rose-700',
                                                $status === 'selesai' => 'bg-sky-700',
                                                default => 'bg-charcoal',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $statusDotClass }}"></span>
                                            {{ $item->status->value }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        @if ($item->status->value === 'Menunggu')
                                            <form method="POST" action="{{ route('admin.peminjaman.approve', $item) }}" class="inline-block">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="mr-2 rounded-lg bg-emerald-700 px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-emerald-800">Setujui</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.peminjaman.reject', $item) }}" class="inline-block">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="rounded-lg border border-rose-700/30 bg-rose-700/10 px-3 py-1.5 text-xs font-semibold text-rose-700 transition-colors hover:bg-rose-700/20">Tolak</button>
                                            </form>
                                        @else
                                            <a href="{{ route('admin.peminjaman.show', $item) }}" class="rounded-lg border border-border bg-ivory px-3 py-1.5 text-xs font-semibold text-charcoal transition-colors hover:border-taupe/40 hover:bg-taupe/10">Detail</a>
                                        @endif
                                        <form method="POST" action="{{ route('admin.peminjaman.destroy', $item) }}" class="inline-block" onsubmit="return confirm('Hapus data peminjaman ini secara permanen?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-rose-700/30 bg-rose-700/10 px-3 py-1.5 text-xs font-semibold text-rose-700 transition-colors hover:bg-rose-700/20">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-border bg-cream px-6 py-4">
                    {{ $peminjaman->links() }}
                </div>

            @endif

        </div>

    </div>
@endsection

