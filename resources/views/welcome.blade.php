<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>识字 · Chinese Reader</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css2?family=Noto+Serif+SC:wght@400;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
    <main class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-gradient-to-b from-[#fffdf8] via-paper to-paper2 p-8 text-ink">

        {{-- Faded background character --}}
        <div aria-hidden="true"
             class="pointer-events-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-[48%] select-none font-serifsc text-[min(72vw,52rem)] font-bold leading-none text-ink opacity-[0.035]">
            读
        </div>

        {{-- Content --}}
        <div class="relative z-10 max-w-xl text-center motion-safe:animate-rise">
            <p class="mb-6 text-xs font-semibold uppercase tracking-[0.28em] text-seal">
                汉字阅读 · Learn by reading
            </p>

            <h1 class="relative inline-block font-serifsc text-[clamp(4rem,18vw,9rem)] font-bold leading-none tracking-[0.06em]">
                识字
                <span class="absolute -right-4 top-2 h-3 w-3 rotate-45 rounded-sm bg-seal"></span>
            </h1>

            <p class="mt-4 text-sm uppercase tracking-[0.36em] text-ink-soft">Chinese Reader</p>

            <p class="mx-auto mt-7 max-w-md text-lg leading-relaxed text-ink-soft">
                Stories woven entirely from the characters you already know —
                and a single tap to take on the next one.
            </p>

            <div class="mt-10 flex flex-wrap justify-center gap-4">
                @auth
                    <a href="{{ url('/dashboard') }}"
                       class="inline-flex items-center justify-center rounded-full bg-seal px-7 py-3.5 font-semibold text-white shadow-[0_10px_22px_-10px_rgba(192,57,43,0.55)] transition hover:-translate-y-0.5 hover:bg-seal-deep">
                        继续阅读 · Continue
                    </a>
                @else
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                           class="inline-flex items-center justify-center rounded-full bg-seal px-7 py-3.5 font-semibold text-white shadow-[0_10px_22px_-10px_rgba(192,57,43,0.55)] transition hover:-translate-y-0.5 hover:bg-seal-deep">
                            开始阅读 · Start reading
                        </a>
                    @endif
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center justify-center rounded-full border border-line bg-white/60 px-7 py-3.5 font-semibold text-ink transition hover:-translate-y-0.5 hover:border-ink-soft">
                            登录 · Log in
                        </a>
                    @endif
                @endauth
            </div>
        </div>

        <footer class="absolute bottom-6 text-xs tracking-[0.12em] text-ink-soft">johngu.io</footer>
    </main>
</body>
</html>