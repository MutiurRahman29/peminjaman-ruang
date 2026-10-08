@extends('layouts.app')
@section('title', 'Edit Pengguna')
@section('content')
<div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
    <nav class="mb-6 flex items-center gap-2 text-xs font-medium text-muted">
        <a href="{{ route('dashboard') }}" class="hover:text-taupe">Dashboard</a>
        <span>/</span>
        <a href="{{ route('admin.users.index') }}" class="hover:text-taupe">Kelola Pengguna</a>
        <span>/</span>
        <span class="text-charcoal">Edit Pengguna</span>
    </nav>
    <section class="glass-card rounded-[1.75rem] p-6 sm:p-8">
        <div class="mb-8">
            <span class="eyebrow"><span class="h-2 w-2 rounded-full bg-sky-700"></span>Perbarui data</span>
            <h1 class="section-title text-3xl sm:text-4xl">Edit Pengguna</h1>
            <p class="mt-3 text-sm leading-6 text-muted">Perbarui informasi akun dan hak akses pengguna <strong class="text-charcoal">{{ $user->nama }}</strong>.</p>
        </div>
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
            @csrf
            @method('PUT')
            <div>
                <label for="nama" class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-muted">Nama Lengkap <span class="text-rose-700">*</span></label>
                <input id="nama" name="nama" type="text" value="{{ old('nama', $user->nama) }}" maxlength="100" required placeholder="Contoh: Ahmad Subagja" class="form-field @error('nama') border-rose-700/50 focus:border-rose-700 focus:ring-rose-700/20 @enderror">
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
                <input id="username" name="username" type="text" value="{{ old('username', $user->username) }}" maxlength="50" required placeholder="ahmad_subagja" class="form-field @error('username') border-rose-700/50 focus:border-rose-700 focus:ring-rose-700/20 @enderror">
                @error('username')
                    <div class="mt-2 flex items-center gap-1.5 text-xs text-rose-700">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>
            <div class="rounded-2xl border border-taupe/20 bg-taupe/5 p-4 text-sm text-taupe">
                <div class="flex items-start gap-3">
                    <svg class="mt-0.5 h-5 w-5 text-taupe" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                    <span>Password boleh dikosongkan jika Anda tidak ingin mengubah password pengguna ini.</span>
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="password" class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-muted">Password Baru <span class="text-xs font-normal text-muted">(Opsional)</span></label>
                    <input id="password" name="password" type="password" placeholder="••••••••" class="form-field @error('password') border-rose-700/50 focus:border-rose-700 focus:ring-rose-700/20 @enderror">
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
                    <label for="password_confirmation" class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-muted">Konfirmasi Password Baru</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" placeholder="••••••••" class="form-field @error('password_confirmation') border-rose-700/50 focus:border-rose-700 focus:ring-rose-700/20 @enderror">
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
                    @foreach ($roleOptions as $role)
                        <option value="{{ $role->value }}" @selected(old('role', $user->role->value ?? $user->role) === $role->value)> {{ $role->value }} </option>
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
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    Perbarui Data
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
