<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Privacy · 识字 Let's Read Chinese!</title>
    <meta name="description" content="What letsreadchinese.com collects, why, and where it goes — in plain English.">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css2?family=Noto+Serif+SC:wght@400;600;700&display=swap" rel="stylesheet">

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

<body class="antialiased">
    <main class="relative min-h-screen overflow-hidden bg-gradient-to-b from-[#fffdf8] via-paper to-paper2 px-6 py-16 text-ink sm:px-8">

        {{-- Faded background character --}}
        <div aria-hidden="true"
             class="pointer-events-none fixed left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 select-none font-serifsc text-[min(72vw,48rem)] font-bold leading-none text-ink opacity-[0.03]">
            私
        </div>

        <div class="relative z-10 mx-auto max-w-2xl motion-safe:animate-rise">

            {{-- Back link --}}
            <a href="{{ url('/') }}"
               class="inline-flex items-center gap-2 text-sm text-ink-soft transition hover:text-seal">
                <span aria-hidden="true">←</span> 识字 · Let's Read Chinese!
            </a>

            {{-- Header --}}
            <header class="mt-10">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-seal">隐私 · Privacy</p>
                <h1 class="relative mt-3 inline-block font-serifsc text-4xl font-bold leading-tight sm:text-5xl">
                    Privacy Policy
                    <span class="absolute -right-4 top-1 h-2.5 w-2.5 rotate-45 rounded-sm bg-seal"></span>
                </h1>
                <p class="mt-3 text-sm text-ink-soft">Effective date: June 3, 2026</p>
            </header>

            {{-- The short version --}}
            <div class="mt-10 rounded-2xl border border-line bg-white/70 p-6 shadow-sm">
                <h2 class="font-serifsc text-lg font-semibold">The short version</h2>
                <p class="mt-2 leading-relaxed text-ink-soft">
                    This site does one thing: it writes short Chinese texts using only the characters
                    you give it. To do that, we keep your account email, the character lists and texts
                    you create, and standard usage analytics. We don't sell your data, we don't share
                    it with advertisers, and there are no ads here.
                </p>
            </div>

            {{-- Who we are --}}
            <section class="mt-12">
                <h2 class="flex items-center gap-3 font-serifsc text-xl font-semibold">
                    <span class="h-2 w-2 shrink-0 rotate-45 rounded-[1px] bg-seal"></span>
                    Who we are
                </h2>
                <p class="mt-3 leading-relaxed text-ink-soft">
                    识字 · Let's Read Chinese! (letsreadchinese.com) is a small independent project
                    created and run by John Gu. If you have any question about this policy or your
                    data, email
                    {{-- TODO: replace with your real contact address --}}
                    <a href="mailto:hello@letsreadchinese.com" class="text-seal underline decoration-seal/30 underline-offset-4 transition hover:decoration-seal">hello@letsreadchinese.com</a>
                    and a human (the same one who built this) will answer.
                </p>
            </section>

            {{-- What we collect --}}
            <section class="mt-12">
                <h2 class="flex items-center gap-3 font-serifsc text-xl font-semibold">
                    <span class="h-2 w-2 shrink-0 rotate-45 rounded-[1px] bg-seal"></span>
                    What we collect
                </h2>
                <ul class="mt-3 space-y-3 pl-5 leading-relaxed text-ink-soft marker:text-seal" style="list-style-type: disc;">
                    <li>
                        <span class="font-semibold text-ink">Account details.</span>
                        If you register: your name, email address, and password. Passwords are stored
                        hashed — we never see or store them in plain text.
                    </li>
                    <li>
                        <span class="font-semibold text-ink">Your content.</span>
                        The character lists you enter or save, and the texts generated for you.
                        This is the heart of the service, and it stays tied to your account so you
                        can come back to it.
                    </li>
                    <li>
                        <span class="font-semibold text-ink">Usage data.</span>
                        Basic records of how the site is used — for example when texts are generated —
                        and standard analytics: pages visited, device and browser type, approximate
                        (city-level) location, and how you found the site.
                    </li>
                    <li>
                        <span class="font-semibold text-ink">Technical data.</span>
                        Like nearly every website, our hosting and security providers keep short-lived
                        request logs that include your IP address and browser information. We use these
                        only for security, debugging, and abuse prevention.
                    </li>
                </ul>
            </section>

            {{-- How we use it --}}
            <section class="mt-12">
                <h2 class="flex items-center gap-3 font-serifsc text-xl font-semibold">
                    <span class="h-2 w-2 shrink-0 rotate-45 rounded-[1px] bg-seal"></span>
                    How we use it
                </h2>
                <p class="mt-3 leading-relaxed text-ink-soft">
                    To run the service: generating texts from your characters, keeping your character
                    library attached to your account, and remembering that you're logged in. To improve
                    the service: understanding which features get used and where people get stuck. And
                    to protect the service: rate-limiting, blocking abuse, and keeping the site up.
                    That's the whole list — we don't use your data for advertising, profiling, or
                    anything unrelated to reading Chinese.
                </p>
            </section>

            {{-- Where your data goes --}}
            <section class="mt-12">
                <h2 class="flex items-center gap-3 font-serifsc text-xl font-semibold">
                    <span class="h-2 w-2 shrink-0 rotate-45 rounded-[1px] bg-seal"></span>
                    Where your data goes
                </h2>
                <p class="mt-3 leading-relaxed text-ink-soft">
                    We use a small number of service providers to run the site:
                </p>
                <ul class="mt-3 space-y-3 pl-5 leading-relaxed text-ink-soft marker:text-seal" style="list-style-type: disc;">
                    <li>
                        <span class="font-semibold text-ink">Google Analytics</span> — anonymous-ish usage
                        statistics so we can see how the site is doing. You can block it with any
                        content blocker or Google's own
                        <a href="https://tools.google.com/dlpage/gaoptout" class="text-seal underline decoration-seal/30 underline-offset-4 transition hover:decoration-seal" rel="noopener" target="_blank">opt-out add-on</a>
                        — the site works fine without it.
                        <a href="https://policies.google.com/privacy" class="text-seal underline decoration-seal/30 underline-offset-4 transition hover:decoration-seal" rel="noopener" target="_blank">Google's privacy policy</a>.
                    </li>
                    <li>
                        <span class="font-semibold text-ink">Cloudflare</span> — sits in front of the site
                        for speed and security, so all traffic (including your IP address) passes through it.
                        <a href="https://www.cloudflare.com/privacypolicy/" class="text-seal underline decoration-seal/30 underline-offset-4 transition hover:decoration-seal" rel="noopener" target="_blank">Cloudflare's privacy policy</a>.
                    </li>
                    <li>
                        <span class="font-semibold text-ink">DreamHost</span> — hosts the site and database.
                        <a href="https://www.dreamhost.com/legal/privacy-policy/" class="text-seal underline decoration-seal/30 underline-offset-4 transition hover:decoration-seal" rel="noopener" target="_blank">DreamHost's privacy policy</a>.
                    </li>
                    <li>
                        <span class="font-semibold text-ink">Bunny Fonts</span> — serves this site's fonts
                        and is designed not to track visitors.
                        <a href="https://fonts.bunny.net/about" class="text-seal underline decoration-seal/30 underline-offset-4 transition hover:decoration-seal" rel="noopener" target="_blank">About Bunny Fonts</a>.
                    </li>
                </ul>
                <p class="mt-3 leading-relaxed text-ink-soft">
                    We never sell your personal data, and we don't share it with anyone beyond the
                    providers above.
                </p>
            </section>

            {{-- Cookies --}}
            <section class="mt-12">
                <h2 class="flex items-center gap-3 font-serifsc text-xl font-semibold">
                    <span class="h-2 w-2 shrink-0 rotate-45 rounded-[1px] bg-seal"></span>
                    Cookies
                </h2>
                <p class="mt-3 leading-relaxed text-ink-soft">
                    We use a few essential cookies to keep you logged in and to protect forms from
                    forgery (the standard Laravel session and CSRF cookies), plus Google Analytics
                    cookies for the usage statistics described above, and Cloudflare may set a cookie
                    for security purposes. Blocking the analytics cookies won't break anything.
                </p>
            </section>

            {{-- Trying it without an account --}}
            <section class="mt-12">
                <h2 class="flex items-center gap-3 font-serifsc text-xl font-semibold">
                    <span class="h-2 w-2 shrink-0 rotate-45 rounded-[1px] bg-seal"></span>
                    Trying it without an account
                </h2>
                <p class="mt-3 leading-relaxed text-ink-soft">
                    You can generate a text from the homepage without registering. In that case the
                    characters you enter are used to generate your text and are not linked to any
                    account or identity — only the ordinary technical logs described above apply.
                </p>
            </section>

            {{-- Your data, your call --}}
            <section class="mt-12">
                <h2 class="flex items-center gap-3 font-serifsc text-xl font-semibold">
                    <span class="h-2 w-2 shrink-0 rotate-45 rounded-[1px] bg-seal"></span>
                    Your data, your call
                </h2>
                <p class="mt-3 leading-relaxed text-ink-soft">
                    We keep your account data for as long as you have an account. Email us at any time
                    to ask what we hold about you, to correct it, or to delete your account entirely —
                    deletion removes your account details, saved character lists, and generated texts.
                    No hoops, no retention tricks.
                </p>
            </section>

            {{-- Children --}}
            <section class="mt-12">
                <h2 class="flex items-center gap-3 font-serifsc text-xl font-semibold">
                    <span class="h-2 w-2 shrink-0 rotate-45 rounded-[1px] bg-seal"></span>
                    Children
                </h2>
                <p class="mt-3 leading-relaxed text-ink-soft">
                    The site isn't directed at children under 13, and we don't knowingly collect
                    personal information from them. If you believe a child has created an account,
                    contact us and we'll delete it.
                </p>
            </section>

            {{-- Changes --}}
            <section class="mt-12">
                <h2 class="flex items-center gap-3 font-serifsc text-xl font-semibold">
                    <span class="h-2 w-2 shrink-0 rotate-45 rounded-[1px] bg-seal"></span>
                    Changes to this policy
                </h2>
                <p class="mt-3 leading-relaxed text-ink-soft">
                    If the way we handle data changes — for example, if we add a new provider — we'll
                    update this page and the effective date at the top. Significant changes will be
                    noted on the site.
                </p>
            </section>

            {{-- Footer --}}
            <footer class="mt-16 border-t border-line pt-6 text-xs tracking-[0.12em] text-ink-soft">
                <a href="{{ url('/') }}" class="transition hover:text-seal">识字 · Let's Read Chinese!</a>
                <span class="mx-2" aria-hidden="true">·</span>
                Created by John Gu
            </footer>
        </div>
    </main>
</body>
</html>