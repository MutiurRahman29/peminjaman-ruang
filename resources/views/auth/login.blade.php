<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Masuk ke Sistem Pengelolaan Ruang dan Fasilitas.">
    <title>Masuk – Sistem Pengelolaan Ruang dan Fasilitas</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-ivory text-charcoal antialiased">
    <div class="relative flex min-h-dvh items-center justify-center px-4 py-10 sm:px-6">
        <a href="{{ route('home') }}" class="absolute left-4 top-4 grid h-10 w-10 place-items-center rounded-full border border-black bg-ivory text-xl font-bold text-charcoal transition hover:bg-cream focus:outline-none focus:ring-4 focus:ring-taupe/20 sm:left-6 sm:top-6" aria-label="Kembali ke beranda">
            ←
        </a>

        <main class="w-full max-w-md">
            <section class="border border-border bg-cream p-6 sm:p-8" aria-labelledby="login-title">
                <div class="mb-7 text-center">
                    <h1 id="login-title" class="mt-3 text-2xl font-bold tracking-tight text-charcoal sm:text-3xl">Masuk ke akun</h1>
                    <p class="mt-2 text-sm leading-6 text-muted">Gunakan akun Anda untuk mengelola ruang dan fasilitas.</p>
                </div>

                <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="username" class="mb-2 block text-sm font-medium text-charcoal">Username</label>
                        <input
                            id="username"
                            name="username"
                            type="text"
                            value="{{ old('username') }}"
                            required
                            autofocus
                            autocomplete="username"
                            class="@class(['w-full rounded-xl border border-black bg-white px-4 py-5 text-sm text-charcoal outline-none transition focus:border-taupe focus:ring-4 focus:ring-taupe/10', 'border-rose-700/60' => $errors->has('username')])"
                        >
                        @error('username')
                            <p class="mt-2 text-xs font-medium text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-medium text-charcoal">Password</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="current-password"
                            class="@class(['w-full rounded-xl border border-black bg-white px-4 py-5 text-sm text-charcoal outline-none transition focus:border-taupe focus:ring-4 focus:ring-taupe/10', 'border-rose-700/60' => $errors->has('password')])"
                        >
                        @error('password')
                            <p class="mt-2 text-xs font-medium text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <label for="remember" class="flex cursor-pointer items-center gap-2 text-sm text-muted">
                            <input id="remember" name="remember" type="checkbox" value="1" @checked(old('remember')) class="h-4 w-4 rounded border-black bg-ivory text-taupe focus:ring-taupe/30">
                            Ingat saya
                        </label>
                    </div>

                    <button type="submit" class="w-full rounded-xl bg-charcoal px-4 py-3 text-sm font-bold text-ivory transition-colors hover:brightness-110 focus:outline-none focus:ring-4 focus:ring-taupe/20">
                        Masuk ke Dashboard
                    </button>
                </form>
            </section>
        </main>
    </div>
</body>

</html>
