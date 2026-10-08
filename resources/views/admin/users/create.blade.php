@extends('layouts.app')
@section('title', 'Tambah Pengguna')
@section('content')
<div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
    <nav class="mb-6 flex items-center gap-2 text-xs font-medium text-muted">
        <a href="{{ route('dashboard') }}" class="hover:text-taupe">Dashboard</a>
        <span>/</span>
        <a href="{{ route('admin.users.index') }}" class="hover:text-taupe">Kelola Pengguna</a>
        <span>/</span>
        <span class="text-charcoal">Tambah Pengguna</span>
    </nav>
    <section class="glass-card rounded-[1.75rem] p-6 sm:p-8">
        <div class="mb-8">
            <span class="eyebrow"><span class="h-2 w-2 rounded-full bg-charcoal"></span>Akun baru</span>
            <h1 class="section-title text-3xl sm:text-4xl">Tambah Pengguna</h1>
            <p class="mt-3 text-sm leading-6 text-muted">Buat akun baru dan tentukan hak akses ke dalam sistem.</p>
        </div>
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
            @csrf
            <div>
                <label for="nama" class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-muted">Nama Lengkap <span class="text-rose-700">*</span></label>
                <input id="nama" name="nama" type="text" value="{{ old('nama') }}" maxlength="100" required placeholder="Contoh: Ahmad Subagja" class="form-field @error('nama') border-rose-700/50 focus:border-rose-700 focus:ring-rose-700/20 @enderror">
                @error('nama')
                    <div class="mt-2 flex items-center gap-1.5 text-xs text-rose-700">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>
            <div>
                <label for="username" class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-muted">Username <span class="text-rose-700">*</span></label>
                <input id="username" name="username" type="text" value="{{ old('username') }}" maxlength="50" required placeholder="ahmad_subagja" class="form-field @error('username') border-rose-700/50 focus:border-rose-700 focus:ring-rose-700/20 @enderror">
                @error('username')
                    <div class="mt-2 flex items-center gap-1.5 text-xs text-rose-700">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="password" class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-muted">Password <span class="text-rose-700">*</span></label>
                    <input id="password" name="password" type="password" required placeholder="••••••••" class="form-field @error('password') border-rose-700/50 focus:border-rose-700 focus:ring-rose-700/20 @enderror">
                    @error('password')
                        <div class="mt-2 flex items-center gap-1.5 text-xs text-rose-700">
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                            </svg>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-muted">Konfirmasi Password <span class="text-rose-700">*</span></label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required placeholder="••••••••" class="form-field @error('password_confirmation') border-rose-700/50 focus:border-rose-700 focus:ring-rose-700/20 @enderror">
                    @error('password_confirmation')
                        <div class="mt-2 flex items-center gap-1.5 text-xs text-rose-700">
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                            </svg>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror
                </div>
            </div>
            <div>
                <label for="role" class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-muted">Role / Hak Akses <span class="text-rose-700">*</span></label>
                <select id="role" name="role" required class="form-field @error('role') border-rose-700/50 focus:border-rose-700 focus:ring-rose-700/20 @enderror">
                    <option value="" disabled @selected(!old('role'))>Pilih role pengguna</option>
                    @foreach ($roleOptions as $role)
                        <option value="{{ $role->value }}" @selected(old('role') === $role->value)> {{ $role->value }} </option>
                    @endforeach
                </select>
                @error('role')
                    <div class="mt-2 flex items-center gap-1.5 text-xs text-rose-700">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>
            <div class="flex flex-col-reverse gap-3 border-t border-border pt-6 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.users.index') }}" class="soft-button">Batal</a>
                <button type="submit" class="primary-button">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                    </svg>
                    Simpan Pengguna
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
