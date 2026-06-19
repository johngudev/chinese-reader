<x-app-layout>
    <x-slot name="title">Premium</x-slot>

    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            会员 · Premium
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">

            {{-- ── Hero ─────────────────────────────────────── --}}
            <header class="mb-8 text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-seal">
                    会员 · Premium
                </p>
                <h1 class="mt-1 font-serifsc text-3xl font-bold text-gray-900 sm:text-4xl">
                    Read more, every day
                </h1>
                <p class="mx-auto mt-3 max-w-xl text-gray-600">
                    Free accounts get 5 generations per day. Go Premium for unlimited
                    reading and control over the texts you generate.
                </p>
            </header>

            {{-- ── Pricing card ─────────────────────────────── --}}
            <section class="mx-auto max-w-md rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200">

                <div class="mb-4 flex items-center gap-2">
                    <span class="grid h-6 w-6 place-items-center rounded-md bg-seal font-serifsc text-sm text-white">会</span>
                    <span class="text-xs uppercase tracking-[0.16em] text-gray-500">Premium plan</span>
                </div>

                <p class="font-serifsc text-4xl font-bold text-gray-900">
                    $8<span class="ml-1 text-lg font-medium text-gray-400">/ month</span>
                </p>

                <ul class="mt-6 flex flex-col gap-3">
                    <li class="flex items-start gap-3 rounded-xl px-3 py-2.5 ring-1 ring-gray-200">
                        <span class="shrink-0 font-serifsc text-xl leading-none text-seal">✓</span>
                        <span class="text-sm text-gray-700">Unlimited text generations</span>
                    </li>
                    <li class="flex items-start gap-3 rounded-xl px-3 py-2.5 ring-1 ring-gray-200">
                        <span class="shrink-0 font-serifsc text-xl leading-none text-seal">✓</span>
                        <span class="text-sm text-gray-700">Greater text variety — news, stories, articles, dialogues</span>
                    </li>
                    <li class="flex items-start gap-3 rounded-xl px-3 py-2.5 ring-1 ring-gray-200">
                        <span class="shrink-0 font-serifsc text-xl leading-none text-seal">✓</span>
                        <span class="text-sm text-gray-700">Cancel anytime</span>
                    </li>
                </ul>

                <form method="POST" action="{{ route('subscribe') }}" class="mt-6">
                    @csrf
                    <button type="submit" 
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-seal px-6 py-3 font-semibold text-white transition hover:opacity-90">
                        升级 · Upgrade — $8/month
                    </button>
                </form>
                
            </section>

            {{-- ── Back link ────────────────────────────────── --}}
            <p class="mt-8 text-center">
                <a href="{{ route('generate') }}" class="text-sm text-seal hover:underline">
                    ← Back to generating
                </a>
            </p>

        </div>
    </div>
</x-app-layout>