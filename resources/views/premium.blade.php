<x-app-layout>
    <x-slot name="title">Premium</x-slot>

    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            会员 · Premium
        </h2>
    </x-slot>

    <div class="relative overflow-hidden py-10">

        {{-- ── Soft background wash ─────────────────────── --}}
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10">
            <div class="absolute inset-x-0 top-0 h-80 bg-gradient-to-b from-paper2/80 to-transparent"></div>
            <span class="absolute -top-10 left-1/2 -translate-x-1/2 select-none font-serifsc text-[16rem] leading-none text-seal/[0.04]">读</span>
        </div>

        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">

            {{-- ── Hero ─────────────────────────────────────── --}}
            <header class="mb-8 text-center animate-rise">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-seal">
                    会员 · Premium
                </p>
                <h1 class="mt-1 font-serifsc text-3xl font-bold text-gray-900 sm:text-4xl">
                    Read more, every day
                </h1>
                <p class="mx-auto mt-3 max-w-xl text-gray-600">
                    Get unlimited text generations, full access to your saved-words list, and more.
                </p>
            </header>

            {{-- ── Pricing card ─────────────────────────────── --}}
            <section class="relative mx-auto max-w-md animate-rise rounded-2xl bg-white p-6 shadow-lg ring-1 ring-seal/20 sm:p-8">

                {{-- Discount ribbon --}}
                <span class="absolute -top-3 right-6 rounded-full bg-seal px-3 py-1 text-xs font-semibold uppercase tracking-wide text-white shadow-sm">
                    限时 · Summer Sale - 47% Off
                </span>

                <div class="mb-4 flex items-center gap-2">
                    <span class="grid h-6 w-6 place-items-center rounded-md bg-seal font-serifsc text-sm text-white">会</span>
                    <span class="text-xs uppercase tracking-[0.16em] text-gray-500">Premium plan</span>
                </div>

                <p class="flex items-baseline gap-2">
                    <span class="text-lg font-medium text-gray-400 line-through decoration-seal/60 decoration-2">$15</span>
                    <span class="font-serifsc text-5xl font-bold text-gray-900">$8</span>
                    <span class="text-lg font-medium text-gray-400">/ month</span>
                </p>
                <p class="mt-1 text-xs font-medium text-seal">
                    Summer flash sale — locked in for as long as you stay subscribed.
                </p>

                <ul class="mt-6 flex flex-col gap-3">
                    <li class="flex items-start gap-3 rounded-xl bg-paper/60 px-3 py-2.5 ring-1 ring-line">
                        <span class="shrink-0 font-serifsc text-xl leading-none text-seal">✓</span>
                        <span class="text-sm text-gray-700">
                            <strong class="font-semibold text-gray-900">Unlimited text generations</strong><br>
                            <span class="text-gray-500">No daily cap. Read at your own pace.</span>
                        </span>
                    </li>
                    <li class="flex items-start gap-3 rounded-xl bg-paper/60 px-3 py-2.5 ring-1 ring-line">
                        <span class="shrink-0 font-serifsc text-xl leading-none text-seal">✓</span>
                        <span class="text-sm text-gray-700">
                            <strong class="font-semibold text-gray-900">Choose your text style</strong><br>
                            <span class="text-gray-500">New text generation options: news, stories, articles, dialogues.</span>
                        </span>
                    </li>
                    <li class="flex items-start gap-3 rounded-xl bg-paper/60 px-3 py-2.5 ring-1 ring-line">
                        <span class="shrink-0 font-serifsc text-xl leading-none text-seal">✓</span>
                        <span class="text-sm text-gray-700">
                            <strong class="font-semibold text-gray-900">Your full saved-words list</strong><br>
                            <span class="text-gray-500">Every word you've ever saved. Browse and review them all.</span>
                        </span>
                    </li>
                    <li class="flex items-start gap-3 rounded-xl bg-paper/60 px-3 py-2.5 ring-1 ring-line">
                        <span class="shrink-0 font-serifsc text-xl leading-none text-seal">✓</span>
                        <span class="text-sm text-gray-700">
                            <strong class="font-semibold text-gray-900">Printable stories</strong><br>
                            <span class="text-gray-500">Take your reading off-screen. Print any text, cleanly formatted.</span>
                        </span>
                    </li>
                </ul>

                <form method="POST" action="{{ route('subscribe') }}" class="mt-6">
                    @csrf
                    <button type="submit"
                        onclick="gtag('event', 'upgrade_click', { transport_type: 'beacon' })"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-seal px-6 py-3.5 text-lg font-semibold text-white shadow-md transition hover:bg-seal-deep hover:shadow-lg">
                        升级 · Upgrade — $8/month
                    </button>
                </form>

                <p class="mt-3 text-center text-xs text-gray-400">
                    Cancel anytime, in one click. No questions asked.
                </p>

            </section>

            {{-- ── Reassurance line ─────────────────────────── --}}
            <p class="mx-auto mt-8 max-w-md text-center text-sm text-gray-500">
                Reading a little every day is the fastest path to fluency.
                Premium makes sure nothing gets in your way.
            </p>

            {{-- ── Back link ────────────────────────────────── --}}
            <p class="mt-6 text-center">
                <a href="{{ route('generate') }}" class="text-sm text-seal hover:underline">
                    ← Back to generating
                </a>
            </p>

        </div>
    </div>
    <script>
        //Log premium interest
        window.addEventListener('load', () => {
            gtag('event', 'premium_view');
        });
    </script>
</x-app-layout>
