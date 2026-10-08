@extends('layouts.app')

@section('title', 'Ajukan Peminjaman')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
        <nav class="mb-7 flex flex-wrap items-center gap-2 text-xs font-medium text-muted" aria-label="Navigasi breadcrumb">
            <a href="{{ route('dashboard') }}" class="hover:text-taupe">Dashboard</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('peminjam.peminjaman.index') }}" class="hover:text-taupe">Riwayat Peminjaman</a>
            <span aria-hidden="true">/</span>
            <span class="text-charcoal">Ajukan Peminjaman</span>
        </nav>

        <header class="mb-8 max-w-3xl">
            <p class="section-eyebrow">Pilih kebutuhanmu</p>
            <h1 class="text-3xl font-extrabold tracking-[-0.04em] text-charcoal sm:text-4xl">Ajukan peminjaman dengan cepat.</h1>
            <p class="mt-4 text-sm leading-6 text-muted sm:text-base">Pilih ruangan yang sesuai, tentukan jadwal, dan sesuaikan fasilitas tambahan yang dibutuhkan.</p>
        </header>

        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-rose-700/20 bg-rose-700/10 p-4" role="alert" aria-live="polite">
                <div class="flex items-start gap-3">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-rose-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-rose-700">Periksa kembali formulir</p>
                        <p class="mt-1 text-xs leading-5 text-rose-700/80">Tautan merah menunjukkan field yang belum lengkap atau tidak valid.</p>
                    </div>
                </div>
            </div>
        @endif

        @if ($ruangan->isEmpty())
            <section class="glass-card flex min-h-80 flex-col items-center justify-center rounded-[1.75rem] p-8 text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-cream text-muted">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </div>
                <h2 class="mt-5 text-lg font-semibold text-charcoal">Tidak ada ruangan tersedia</h2>
                <p class="mt-2 max-w-sm text-sm text-muted">Belum ada ruangan yang dapat dipinjam pada saat ini.</p>
            </section>
        @else
            <form id="peminjaman-form" method="POST" action="{{ route('peminjam.peminjaman.store') }}" class="grid gap-7 lg:grid-cols-[minmax(0,1.15fr)_minmax(340px,0.85fr)]">
                @csrf

                <div class="space-y-7">
                    <section class="glass-card overflow-hidden rounded-[1.75rem]" aria-labelledby="room-selection-title">
                        <div class="border-b border-border px-5 py-5 sm:px-7">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-taupe">Langkah 1</p>
                                    <h2 id="room-selection-title" class="mt-1 text-xl font-bold text-charcoal">Pilih ruangan</h2>
                                </div>
                                <span class="rounded-full bg-cream px-3 py-1 text-xs font-semibold text-muted">{{ $ruangan->count() }} tersedia</span>
                            </div>
                        </div>

                        <div x-data="{ selected: {{ old('id_ruangan') ? json_encode(old('id_ruangan')) : 'null' }} }" class="grid gap-4 p-5 sm:grid-cols-2 sm:p-7">
                            @foreach ($ruangan as $item)
                                <div class="group relative block cursor-pointer overflow-hidden rounded-2xl border border-border bg-ivory transition duration-300 hover:-translate-y-0.5 hover:border-taupe hover:shadow-lg {{ old('id_ruangan') == $item->id_ruangan ? 'border-taupe ring-2 ring-taupe/15' : '' }}">
                                    <input id="ruangan_{{ $item->id_ruangan }}" type="radio" name="id_ruangan" value="{{ $item->id_ruangan }}" class="peer sr-only" aria-label="Pilih {{ $item->nama_ruangan }}" x-model="selected" @checked(old('id_ruangan') == $item->id_ruangan)>
                                    <label for="ruangan_{{ $item->id_ruangan }}" class="absolute inset-0 z-20 cursor-pointer focus-within:ring-2 focus-within:ring-taupe focus-within:ring-offset-2">
                                        <span class="sr-only">Pilih {{ $item->nama_ruangan }}</span>
                                    </label>
                                    <div class="relative aspect-16/10 overflow-hidden bg-cream">
                                        @if ($item->gambar_url)
                                            <img src="{{ $item->gambar_url }}" alt="Foto {{ $item->nama_ruangan }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" width="720" height="440" loading="lazy">
                                        @else
                                            <div class="flex h-full flex-col items-center justify-center gap-2 text-muted" aria-label="Foto ruangan belum tersedia">
                                                <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.25" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5l5.25-5.25a2.25 2.25 0 013.182 0L16.5 16.5m-2.25-2.25l1.318-1.318a2.25 2.25 0 013.182 0L21 15.75M3 6.75A2.25 2.25 0 015.25 4.5h13.5A2.25 2.25 0 0121 6.75v10.5a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 17.25V6.75z" /></svg>
                                                <span class="text-xs font-medium">Foto belum tersedia</span>
                                            </div>
                                        @endif
                                        <span class="absolute left-3 top-3 rounded-full bg-ivory/90 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.12em] text-charcoal shadow-sm backdrop-blur">Tersedia</span>
                                    </div>
                                    <div class="p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <h3 class="text-base font-bold text-charcoal">{{ $item->nama_ruangan }}</h3>
                                                <p class="mt-1 flex items-center gap-1.5 text-xs text-muted">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                                                    {{ $item->lokasi }}
                                                </p>
                                            </div>
                                            <span class="rounded-xl bg-cream px-2.5 py-1.5 text-xs font-bold text-charcoal">{{ $item->kapasitas }} orang</span>
                                        </div>
                                        <div class="mt-4 flex items-center justify-between border-t border-border pt-3 text-xs">
                                            <span x-show="selected != {{ $item->id_ruangan }}" class="font-semibold text-muted">Pilih ruangan</span>
                                            <span x-show="selected == {{ $item->id_ruangan }}" class="font-semibold text-taupe">Dipilih</span>
                                            <span class="flex h-7 w-7 items-center justify-center rounded-full border border-border text-charcoal transition" :class="selected == {{ $item->id_ruangan }} ? 'border-taupe bg-taupe text-ivory' : ''" aria-hidden="true">✓</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('id_ruangan')
                            <div class="mx-5 mb-5 flex items-center gap-1.5 text-xs font-medium text-rose-700 sm:mx-7 sm:mb-7">
                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                                {{ $message }}
                            </div>
                        @enderror
                    </section>

                    <section class="glass-card rounded-[1.75rem] p-5 sm:p-7" aria-labelledby="schedule-title">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-taupe">Langkah 2</p>
                        <h2 id="schedule-title" class="mt-1 text-xl font-bold text-charcoal">Tentukan jadwal</h2>
                        <div class="mt-6 grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="tanggal" class="mb-2 block text-sm font-semibold text-charcoal">Tanggal pelaksanaan <span class="text-rose-700">*</span></label>
                                <input id="tanggal" name="tanggal" type="date" value="{{ old('tanggal') }}" min="{{ now()->toDateString() }}" required aria-invalid="{{ $errors->has('tanggal') ? 'true' : 'false' }} aria-describedby="{{ $errors->has('tanggal') ? 'tanggal-error' : '' }}" class="w-full rounded-2xl border {{ $errors->has('tanggal') ? 'border-rose-700 focus:border-rose-700 focus:ring-rose-700/20' : 'border-border focus:border-taupe focus:ring-taupe/20' }} bg-ivory px-4 py-3.5 text-sm text-charcoal transition focus:outline-none focus:ring-2">
                                @error('tanggal')<p id="tanggal-error" class="mt-2 text-xs font-medium text-rose-700">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="keperluan" class="mb-2 block text-sm font-semibold text-charcoal">Maksud &amp; keperluan <span class="text-rose-700">*</span></label>
                                <textarea id="keperluan" name="keperluan" maxlength="1000" rows="3" required placeholder="Jelaskan agenda atau kegiatan..." aria-invalid="{{ $errors->has('keperluan') ? 'true' : 'false' }} aria-describedby="{{ $errors->has('keperluan') ? 'keperluan-error' : '' }}" class="w-full resize-none rounded-2xl border {{ $errors->has('keperluan') ? 'border-rose-700 focus:border-rose-700 focus:ring-rose-700/20' : 'border-border focus:border-taupe focus:ring-taupe/20' }} bg-ivory px-4 py-3.5 text-sm text-charcoal placeholder-muted transition focus:outline-none focus:ring-2">{{ old('keperluan') }}</textarea>
                                @error('keperluan')<p id="keperluan-error" class="mt-2 text-xs font-medium text-rose-700">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="nama_pemohon" class="mb-2 block text-sm font-semibold text-charcoal">Nama pemohon <span class="text-rose-700">*</span></label>
                                <input id="nama_pemohon" name="nama_pemohon" type="text" value="{{ old('nama_pemohon') }}" autocomplete="name" maxlength="100" required aria-invalid="{{ $errors->has('nama_pemohon') ? 'true' : 'false' }} aria-describedby="{{ $errors->has('nama_pemohon') ? 'nama_pemohon-error' : '' }}" class="w-full rounded-2xl border {{ $errors->has('nama_pemohon') ? 'border-rose-700 focus:border-rose-700 focus:ring-rose-700/20' : 'border-border focus:border-taupe focus:ring-taupe/20' }} bg-ivory px-4 py-3.5 text-sm text-charcoal transition focus:outline-none focus:ring-2">
                                @error('nama_pemohon')<p id="nama_pemohon-error" class="mt-2 text-xs font-medium text-rose-700">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="email_pemohon" class="mb-2 block text-sm font-semibold text-charcoal">Email pemohon <span class="text-rose-700">*</span></label>
                                <input id="email_pemohon" name="email_pemohon" type="email" value="{{ old('email_pemohon') }}" autocomplete="email" maxlength="255" required aria-invalid="{{ $errors->has('email_pemohon') ? 'true' : 'false' }} aria-describedby="{{ $errors->has('email_pemohon') ? 'email_pemohon-error' : '' }}" class="w-full rounded-2xl border {{ $errors->has('email_pemohon') ? 'border-rose-700 focus:border-rose-700 focus:ring-rose-700/20' : 'border-border focus:border-taupe focus:ring-taupe/20' }} bg-ivory px-4 py-3.5 text-sm text-charcoal transition focus:outline-none focus:ring-2">
                                @error('email_pemohon')<p id="email_pemohon-error" class="mt-2 text-xs font-medium text-rose-700">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="whatsapp_pemohon" class="mb-2 block text-sm font-semibold text-charcoal">Nomor WhatsApp <span class="text-rose-700">*</span></label>
                                <input id="whatsapp_pemohon" name="whatsapp_pemohon" type="tel" value="{{ old('whatsapp_pemohon') }}" autocomplete="tel" inputmode="numeric" minlength="10" maxlength="14" required placeholder="62xxxxxxxxxx" aria-invalid="{{ $errors->has('whatsapp_pemohon') ? 'true' : 'false' }} aria-describedby="{{ $errors->has('whatsapp_pemohon') ? 'whatsapp_pemohon-error' : '' }}" class="w-full rounded-2xl border {{ $errors->has('whatsapp_pemohon') ? 'border-rose-700 focus:border-rose-700 focus:ring-rose-700/20' : 'border-border focus:border-taupe focus:ring-taupe/20' }} bg-ivory px-4 py-3.5 text-sm text-charcoal transition focus:outline-none focus:ring-2">
                                @error('whatsapp_pemohon')<p id="whatsapp_pemohon-error" class="mt-2 text-xs font-medium text-rose-700">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="akses_password" class="mb-2 block text-sm font-semibold text-charcoal">Kata sandi akses <span class="text-rose-700">*</span></label>
                                <input id="akses_password" name="akses_password" type="password" autocomplete="new-password" minlength="6" required aria-invalid="{{ $errors->has('akses_password') ? 'true' : 'false' }} aria-describedby="{{ $errors->has('akses_password') ? 'akses_password-error' : '' }}" class="w-full rounded-2xl border {{ $errors->has('akses_password') ? 'border-rose-700 focus:border-rose-700 focus:ring-rose-700/20' : 'border-border focus:border-taupe focus:ring-taupe/20' }} bg-ivory px-4 py-3.5 text-sm text-charcoal transition focus:outline-none focus:ring-2">
                                @error('akses_password')<p id="akses_password-error" class="mt-2 text-xs font-medium text-rose-700">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="akses_password_confirmation" class="mb-2 block text-sm font-semibold text-charcoal">Konfirmasi kata sandi <span class="text-rose-700">*</span></label>
                                <input id="akses_password_confirmation" name="akses_password_confirmation" type="password" autocomplete="new-password" minlength="6" required aria-invalid="{{ $errors->has('akses_password_confirmation') ? 'true' : 'false' }} aria-describedby="{{ $errors->has('akses_password_confirmation') ? 'akses_password_confirmation-error' : '' }}" class="w-full rounded-2xl border {{ $errors->has('akses_password_confirmation') ? 'border-rose-700 focus:border-rose-700 focus:ring-rose-700/20' : 'border-border focus:border-taupe focus:ring-taupe/20' }} bg-ivory px-4 py-3.5 text-sm text-charcoal transition focus:outline-none focus:ring-2">
                                @error('akses_password_confirmation')<p id="akses_password_confirmation-error" class="mt-2 text-xs font-medium text-rose-700">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="jam_mulai" class="mb-2 block text-sm font-semibold text-charcoal">Jam mulai <span class="text-rose-700">*</span></label>
                                <input id="jam_mulai" name="jam_mulai" type="time" value="{{ old('jam_mulai') }}" required step="60" aria-invalid="{{ $errors->has('jam_mulai') ? 'true' : 'false' }} aria-describedby="{{ $errors->has('jam_mulai') ? 'jam_mulai-error' : '' }}" @input="(() => { const start = $event.target.value; const [hour, minute] = start.split(':').map(Number); const end = new Date(2000, 0, 1, hour, minute + 1); const minimum = `${String(end.getHours()).padStart(2, '0')}:${String(end.getMinutes()).padStart(2, '0')}`; const endInput = document.getElementById('jam_selesai'); endInput.min = minimum; if (!endInput.value || endInput.value <= start) { endInput.value = minimum; } })()" class="w-full rounded-2xl border {{ $errors->has('jam_mulai') ? 'border-rose-700 focus:border-rose-700 focus:ring-rose-700/20' : 'border-border focus:border-taupe focus:ring-taupe/20' }} bg-ivory px-4 py-3.5 text-sm text-charcoal transition focus:outline-none focus:ring-2">
                                @error('jam_mulai')<p id="jam_mulai-error" class="mt-2 text-xs font-medium text-rose-700">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="jam_selesai" class="mb-2 block text-sm font-semibold text-charcoal">Jam selesai <span class="text-rose-700">*</span></label>
                                <input id="jam_selesai" name="jam_selesai" type="time" value="{{ old('jam_selesai') }}" required step="60" min="{{ old('jam_mulai') ? \Carbon\Carbon::parse(old('jam_mulai'))->addMinute()->format('H:i') : '' }}" aria-invalid="{{ $errors->has('jam_selesai') ? 'true' : 'false' }} aria-describedby="{{ $errors->has('jam_selesai') ? 'jam_selesai-error' : '' }}" class="w-full rounded-2xl border {{ $errors->has('jam_selesai') ? 'border-rose-700 focus:border-rose-700 focus:ring-rose-700/20' : 'border-border focus:border-taupe focus:ring-taupe/20' }} bg-ivory px-4 py-3.5 text-sm text-charcoal transition focus:outline-none focus:ring-2">
                                @error('jam_selesai')<p id="jam_selesai-error" class="mt-2 text-xs font-medium text-rose-700">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </section>
                </div>

                <aside class="space-y-7">
                    <section class="glass-card overflow-hidden rounded-[1.75rem]" aria-labelledby="facilities-title">
                        <div class="border-b border-border px-5 py-5 sm:px-7">
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-taupe">Langkah 3</p>
                            <h2 id="facilities-title" class="mt-1 text-xl font-bold text-charcoal">Fasilitas tambahan</h2>
                            <p class="mt-2 text-xs leading-5 text-muted">Masukkan jumlah yang dibutuhkan. Jumlah akan divalidasi terhadap stok tersedia.</p>
                        </div>
                        <div class="space-y-3 p-5 sm:p-7">
                            @forelse ($fasilitas as $item)
                                <div class="flex items-center gap-4 rounded-2xl border border-border bg-ivory p-3 transition hover:border-taupe/60">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-cream">
                                        @if ($item->gambar_url)
                                            <img src="{{ $item->gambar_url }}" alt="Foto {{ $item->nama_fasilitas }}" class="h-full w-full object-cover" width="48" height="48" loading="lazy">
                                        @else
                                            <svg class="h-6 w-6 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-label="Foto fasilitas belum tersedia"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <label for="fasilitas_{{ $item->id_fasilitas }}" class="block truncate text-sm font-semibold text-charcoal">{{ $item->nama_fasilitas }}</label>
                                        <p class="mt-0.5 text-[11px] text-muted">{{ $item->jumlah }} unit tersedia · maks. {{ $item->jumlah }} unit</p>
                                    </div>
                                    <input id="fasilitas_{{ $item->id_fasilitas }}" name="fasilitas[{{ $item->id_fasilitas }}]" type="number" min="1" max="{{ $item->jumlah }}" value="{{ old('fasilitas.'.$item->id_fasilitas) }}" placeholder="0" x-on:input="$el.value = $el.value && Number($el.value) > Number($el.max) ? $el.max : $el.value" aria-label="Jumlah {{ $item->nama_fasilitas }}, maksimal {{ $item->jumlah }} unit" class="w-20 rounded-xl border border-border bg-ivory px-3 py-2 text-center text-sm text-charcoal focus:border-taupe focus:outline-none focus:ring-2 focus:ring-taupe/20">
                                </div>
                                @error('fasilitas.'.$item->id_fasilitas)
                                    <p class="-mt-2 text-xs font-medium text-rose-700">{{ $message }}</p>
                                @enderror
                            @empty
                                <div class="rounded-2xl border border-dashed border-border p-5 text-center text-xs leading-5 text-muted">Tidak ada fasilitas tambahan yang tersedia.</div>
                            @endforelse
                        </div>
                    </section>

                    <section class="rounded-[1.75rem] border border-border bg-cream p-5 sm:p-7" aria-labelledby="confirmation-title">
                        <div class="flex items-start gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-charcoal text-ivory">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></svg>
                            </span>
                            <div>
                                <h2 id="confirmation-title" class="text-sm font-bold text-charcoal">Konfirmasi pengajuan</h2>
                                <p class="mt-1 text-xs leading-5 text-muted">Pastikan data ini benar karena peminjaman akan diproses oleh tim yang menanggung proses.</p>
                            </div>
                        </div>
                        <label class="mt-5 flex cursor-pointer items-start gap-3 text-xs leading-5 text-muted">
                            <input id="konfirmasi" name="konfirmasi" type="checkbox" value="1" @checked(old('konfirmasi')) aria-invalid="{{ $errors->has('konfirmasi') ? 'true' : 'false' }} aria-describedby="{{ $errors->has('konfirmasi') ? 'konfirmasi-error' : '' }}" class="mt-0.5 h-4 w-4 rounded border {{ $errors->has('konfirmasi') ? 'border-rose-700' : 'border-border' }} bg-ivory text-taupe focus:ring-taupe/30">
                            <span>Saya menyatakan data peminjaman yang saya masukkan adalah benar.</span>
                        </label>
                        @error('konfirmasi')<p id="konfirmasi-error" class="mt-2 text-xs font-medium text-rose-700">{{ $message }}</p>@enderror
                    </section>

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <a href="{{ route('peminjam.peminjaman.index') }}" class="soft-button">Batal</a>
                        <button type="submit" class="primary-button">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" /></svg>
                            Kirim Pengajuan
                        </button>
                    </div>
                </aside>
            </form>
            @if ($errors->any())
                <script>
                    const firstErrorField = {{ json_encode(array_key_first($errors->all())) }};
                    const firstErrorElement = document.getElementById(firstErrorField);

                    if (firstErrorElement) {
                        firstErrorElement.focus();
                    }
                </script>
            @endif
        @endif
    </div>
@endsection
