@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title.' · 识字 Let\'s Read Chinese' : '识字 · Let\'s Read Chinese' }}</title>

    @isset($meta)
        {{ $meta }}
    @endisset

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+SC:wght@400;600&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-CLDQDHQB7N"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-CLDQDHQB7N');
    </script>
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">

        @auth
            {{-- Logged-in visitors get the full application navigation.
                 Safe to include here: navigation.blade.php's auth()->user()
                 calls only run because we're inside @auth (user is non-null). --}}
            @include('layouts.navigation')
            @include('layouts.loading-modal')
        @else
            {{-- Guests get a simple bar: Home · Log in · Sign up. --}}
            <nav class="border-b border-gray-100 bg-white">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 items-center justify-between">
                        <a href="{{ url('/') }}" class="flex items-center gap-2">
                            <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                            <span class="font-serifsc text-lg text-gray-800">Let's Read Chinese</span>
                        </a>

                        <div class="flex items-center gap-2 sm:gap-3">
                            <a href="{{ url('/') }}"
                               class="rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition hover:text-gray-900">
                                Home
                            </a>
                            <a href="{{ route('login') }}"
                               class="rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition hover:text-gray-900">
                                Log in
                            </a>
                            <a href="{{ route('register') }}"
                               class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                                Sign up
                            </a>
                        </div>
                    </div>
                </div>
            </nav>
        @endauth

        {{-- Optional page heading, mirrors the app layout's header slot. --}}
        @isset($header)
            <header class="bg-white shadow">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <main>
            {{ $slot }}
        </main>
    </div>
</body>
</html>
