<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistem pengelolaan ruang dan fasilitas untuk peminjaman, persetujuan, dan data inventaris.">
    <title>@yield('title', 'Sistem Pengelolaan Ruang dan Fasilitas')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="site-shell min-h-screen font-sans text-muted antialiased">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-100 focus:rounded-xl focus:bg-taupe focus:px-4 focus:py-2 focus:text-sm focus:font-bold focus:text-charcoal">Lewati ke konten utama</a>

    @include('partials.header')

    @if (session('success') || session('error'))
        <div class="fixed right-4 top-24 z-50 flex max-w-sm flex-col gap-2" aria-live="polite" aria-atomic="true">
            @if (session('success'))
                <div class="flex items-start gap-3 rounded-xl border border-emerald-700/20 bg-emerald-700/10 px-4 py-3 text-sm text-emerald-700">
                    <span class="mt-0.5 text-base leading-none" aria-hidden="true">✓</span>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="flex items-start gap-3 rounded-xl border border-rose-700/20 bg-rose-700/10 px-4 py-3 text-sm text-rose-700">
                    <span class="mt-0.5 text-base leading-none" aria-hidden="true">!</span>
                    <p>{{ session('error') }}</p>
                </div>
            @endif
        </div>
    @endif

    <main id="main-content" class="min-h-dvh {{ request()->is('/') ? '' : 'pt-24 sm:pt-28' }}">
        @yield('content')
    </main>

    @include('partials.footer')

    @stack('scripts')
</body>

</html>
