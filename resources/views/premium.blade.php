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
                    Get a bigger variety of texts, full access to your saved-words list, and more.
                </p>
            </header>

            {{-- ── What you get ─────────────────────────────── --}}
            {{-- Sits above the price cards on purpose: the offer should be legible
                 before the numbers are. --}}
            <section class="mx-auto mb-10 max-w-2xl animate-rise">
                <p class="mb-3 text-center text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">
                    包含 · What you get
                </p>

                <ul class="grid gap-3 sm:grid-cols-2">
                    <li class="flex items-start gap-3 rounded-xl bg-white px-4 py-3.5 shadow-sm ring-1 ring-line transition hover:shadow-md">
                        <span class="shrink-0 font-serifsc text-xl leading-none text-seal">✓</span>
                        <span class="text-sm text-gray-700">
                            <strong class="font-semibold text-gray-900">Unlimited text generations</strong><br>
                            <span class="text-gray-500">No daily cap. Read at your own pace.</span>
                        </span>
                    </li>
                    <li class="flex items-start gap-3 rounded-xl bg-white px-4 py-3.5 shadow-sm ring-1 ring-line transition hover:shadow-md">
                        <span class="shrink-0 font-serifsc text-xl leading-none text-seal">✓</span>
                        <span class="text-sm text-gray-700">
                            <strong class="font-semibold text-gray-900">Choose your text style</strong><br>
                            <span class="text-gray-500">New text generation options: stories, articles, dialogues.</span>
                        </span>
                    </li>
                    <li class="flex items-start gap-3 rounded-xl bg-white px-4 py-3.5 shadow-sm ring-1 ring-line transition hover:shadow-md">
                        <span class="shrink-0 font-serifsc text-xl leading-none text-seal">✓</span>
                        <span class="text-sm text-gray-700">
                            <strong class="font-semibold text-gray-900">Your full saved-words list</strong><br>
                            <span class="text-gray-500">Every word you've ever saved. Browse and review them all.</span>
                        </span>
                    </li>
                    <li class="flex items-start gap-3 rounded-xl bg-white px-4 py-3.5 shadow-sm ring-1 ring-line transition hover:shadow-md">
                        <span class="shrink-0 font-serifsc text-xl leading-none text-seal">✓</span>
                        <span class="text-sm text-gray-700">
                            <strong class="font-semibold text-gray-900">Printable stories</strong><br>
                            <span class="text-gray-500">Take your reading off-screen. Print any text, cleanly formatted.</span>
                        </span>
                    </li>
                </ul>
            </section>

            {{-- ── Plan chooser ─────────────────────────────── --}}
            <section class="animate-rise">
                <p class="mb-7 text-center text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">
                    选择方案 · Choose your plan
                </p>

                {{-- Both cards share identical chrome, price type, and button styling.
                     The only difference is the ribbon and the savings pill on annual. --}}
                <div class="grid gap-5 sm:grid-cols-2 sm:items-start">

                    {{-- ── Monthly ──────────────────────────── --}}
                    <div class="relative rounded-2xl bg-white/70 p-6 shadow-sm ring-1 ring-line sm:p-7">

                        <div class="mb-4 mt-1 flex items-center gap-2">
                            <span class="grid h-6 w-6 place-items-center rounded-md bg-seal font-serifsc text-sm text-white">月</span>
                            <span class="text-xs uppercase tracking-[0.16em] text-gray-500">Monthly plan</span>
                        </div>

                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">flat</p>
                        <p class="flex items-baseline gap-1.5">
                            <span class="font-serifsc text-5xl font-bold text-gray-900">$8</span>
                            <span class="text-base font-medium text-gray-400">/ month</span>
                        </p>

                        <p class="mt-2 text-sm text-gray-600">
                            <span class="font-semibold text-gray-900">$96</span> a year, billed monthly
                        </p>

                        <p class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-500 ring-1 ring-line">
                            <span class="font-serifsc leading-none">月</span>
                            Flexible, month to month
                        </p>

                        <form method="POST" action="{{ route('subscribe') }}" class="mt-5">
                            @csrf
                            <input type="hidden" name="plan" value="monthly">
                            <button type="submit"
                                onclick="gtag('event', 'upgrade_click', { plan: 'monthly', transport_type: 'beacon' })"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-seal px-6 py-3.5 text-lg font-semibold text-white shadow-md transition hover:bg-seal-deep hover:shadow-lg">
                                升级 · Go monthly — $8
                            </button>
                        </form>

                        <p class="mt-3 text-center text-xs text-gray-400">
                            Switch to annual anytime.
                        </p>
                    </div>

                    {{-- ── Annual ───────────────────────────── --}}
                    <div class="relative rounded-2xl bg-white/70 p-6 shadow-sm ring-1 ring-line sm:p-7">

                        {{-- Best-value ribbon --}}
                        <span class="absolute -top-3 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-full bg-seal px-3.5 py-1 text-xs font-semibold uppercase tracking-wide text-white shadow-sm">
                            最超值 · Best Value
                        </span>

                        <div class="mb-4 mt-1 flex items-center gap-2">
                            <span class="grid h-6 w-6 place-items-center rounded-md bg-seal font-serifsc text-sm text-white">年</span>
                            <span class="text-xs uppercase tracking-[0.16em] text-gray-500">Annual plan</span>
                        </div>

                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400">FLAT</p>
                        <p class="flex items-baseline gap-1.5">
                            <span class="font-serifsc text-5xl font-bold text-gray-900">$50</span>
                            <span class="text-base font-medium text-gray-400">/ year</span>
                        </p>

                        <p class="mt-2 text-sm text-gray-600">
                            <span class="font-semibold text-gray-900">$4.20</span> per month
                            <!-- <span class="text-gray-400 line-through decoration-seal/50 decoration-2">$96</span> -->
                        </p>

                        <p class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-seal/10 px-3 py-1 text-xs font-semibold text-seal ring-1 ring-seal/20">
                            <span class="font-serifsc leading-none">省</span>
                            Save nearly 50% vs monthly
                        </p>

                        <form method="POST" action="{{ route('subscribe') }}" class="mt-5">
                            @csrf
                            <input type="hidden" name="plan" value="annual">
                            <button type="submit"
                                onclick="gtag('event', 'upgrade_click', { plan: 'annual', transport_type: 'beacon' })"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-seal px-6 py-3.5 text-lg font-semibold text-white shadow-md transition hover:bg-seal-deep hover:shadow-lg">
                                升级 · Get a year — $50
                            </button>
                        </form>

                        <p class="mt-3 text-center text-xs text-gray-400">
                            Two months of reading, free.
                        </p>
                    </div>
                </div>

                <p class="mt-5 text-center text-xs text-gray-400">
                    Both plans include everything above. Cancel anytime, in one click. No questions asked.
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
