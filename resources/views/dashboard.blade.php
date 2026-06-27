<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            我的进度 · Dashboard
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">

            {{-- ── Greeting + Generate ─────────────────────── --}}
            <header class="mb-7 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-seal">
                        <span id="greeting">你好 · Hello</span>, {{ auth()->user()->name }}
                    </p>
                    <h1 class="mt-1 font-serifsc text-3xl font-bold text-gray-900 sm:text-4xl">Welcome back</h1>
                </div>

                <form method="POST" action="{{ url('/generate') }}">
                    @csrf
                    <button type="submit"
                        onclick="document.getElementById('loading-modal').classList.remove('hidden')"
                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-3 font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                        生成新故事 · Generate a text
                    </button>
                </form>
            </header>

            {{-- ── Stat strip ──────────────────────────────── --}}
            <section class="mb-7 grid grid-cols-2 gap-4 lg:grid-cols-4">

                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                    <div class="flex items-center gap-2">
                        <span class="grid h-6 w-6 place-items-center rounded-md bg-seal font-serifsc text-sm text-white">留</span>
                        <span class="text-xs uppercase tracking-[0.16em] text-gray-500">Streak</span>
                    </div>
                    <p class="mt-2 font-serifsc text-4xl font-bold text-gray-900">
                        {{ $streakCurrent }}<span class="ml-1 text-lg font-medium text-gray-400">days</span>
                    </p>
                    <p class="mt-1 text-xs text-gray-500">Longest: {{ $streakLongest }} days</p>
                </div>

                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                    <div class="flex items-center gap-2">
                        <span class="grid h-6 w-6 place-items-center rounded-md bg-seal font-serifsc text-sm text-white">字</span>
                        <span class="text-xs uppercase tracking-[0.16em] text-gray-500">Characters seen</span>
                    </div>
                    <p class="mt-2 font-serifsc text-4xl font-bold text-gray-900">{{ number_format($charsSeen) }}</p>
                    <p class="mt-1 text-xs text-gray-500">unique, across all texts</p>
                </div>

                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                    <div class="flex items-center gap-2">
                        <span class="grid h-6 w-6 place-items-center rounded-md bg-seal font-serifsc text-sm text-white">词</span>
                        <span class="text-xs uppercase tracking-[0.16em] text-gray-500">Saved words</span>
                    </div>
                    <p class="mt-2 font-serifsc text-4xl font-bold text-gray-900">{{ number_format($wordsTotal) }}</p>
                    <p class="mt-1 text-xs {{ $wordsRecent ? 'text-seal' : 'text-gray-500' }}">
                        {{ $wordsRecent }} saved this week
                    </p>
                </div>

                <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                    <div class="flex items-center gap-2">
                        <span class="grid h-6 w-6 place-items-center rounded-md bg-seal font-serifsc text-sm text-white">读</span>
                        <span class="text-xs uppercase tracking-[0.16em] text-gray-500">Texts read</span>
                    </div>
                    <p class="mt-2 font-serifsc text-4xl font-bold text-gray-900">{{ number_format($textsTotal) }}</p>
                    <p class="mt-1 text-xs text-gray-500">since {{ $memberSince }}</p>
                </div>
            </section>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

                {{-- ── LEFT: activity + milestone progress ──── --}}
                <section class="space-y-6 lg:col-span-2">

                    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="font-serifsc text-lg font-bold text-gray-900">Reading activity</h2>
                            <span class="text-xs text-gray-500">Last 7 days</span>
                        </div>
                        <div class="h-56"><canvas id="activity"></canvas></div>
                    </div>

                    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                        <div class="mb-3 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="grid h-6 w-6 place-items-center rounded-md bg-seal font-serifsc text-sm text-white">字</span>
                                <h2 class="font-serifsc text-lg font-bold text-gray-900">Character progress</h2>
                            </div>
                            <span class="text-xs text-gray-500">
                                @if ($progress['maxed'])
                                    里程碑达成 · Maxed out
                                @else
                                    next milestone: {{ number_format($progress['milestone']) }} 字
                                @endif
                            </span>
                        </div>
                        <div class="h-3 w-full overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full bg-seal transition-all" style="width: {{ $progress['pct'] }}%"></div>
                        </div>
                        <p class="mt-2 text-sm text-gray-600">
                            <span class="font-serifsc text-lg font-bold text-gray-900">{{ number_format($progress['current']) }}</span>
                            / {{ number_format($progress['milestone']) }} characters in your library
                        </p>
                    </div>
                </section>

                {{-- ── RIGHT: saved-word deck ───────────────── --}}
                <section class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="grid h-6 w-6 place-items-center rounded-md bg-seal font-serifsc text-sm text-white">词</span>
                            <a href="{{ route('saved-words') }}"><h2 class="font-serifsc text-lg font-bold text-gray-900">Saved Words</h2></a>
                        </div>
                    </div>

                    @if ($deck->isEmpty())
                        <p class="text-sm leading-relaxed text-gray-500">
                            Tap a word while reading to save it — your collection will show up here.
                        </p>
                    @else
                        <ul class="flex flex-col gap-2">
                            @foreach ($deck as $w)
                                <li class="flex items-start gap-3 rounded-xl px-3 py-2.5 ring-1 ring-gray-200">
                                    <span class="shrink-0 font-serifsc text-2xl font-semibold leading-none text-gray-900">{{ $w->word }}</span>
                                    <span class="min-w-0 flex-1 leading-tight">
                                        @if ($w->pinyin)
                                            <span class="text-sm text-seal">{{ $w->pinyin }}</span>
                                        @endif
                                        @if ($w->english)
                                            <span class="block truncate text-xs text-gray-500">{{ $w->english }}</span>
                                        @endif
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            </div>

            {{-- ── My characters (read-only) ────────────── --}}
            <section class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <div class="mb-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="grid h-6 w-6 place-items-center rounded-md bg-seal font-serifsc text-sm text-white">字</span>
                        <h2 class="font-serifsc text-lg font-bold text-gray-900">我的字 · My characters</h2>
                    </div>
                    <span class="text-xs text-gray-500">{{ number_format($progress['current']) }} 字</span>
                </div>

                @if ($myCharacters === '')
                    <p class="text-sm leading-relaxed text-gray-500">
                        No characters saved yet — add the characters you know to get personalized texts.
                    </p>
                @else
                    <div id="my-characters"
                        class="max-h-44 overflow-y-auto rounded-xl bg-gray-50 p-3.5 font-serifsc text-lg leading-loose text-gray-800 ring-1 ring-gray-100">{{ $myCharacters }}</div>
                @endif

                <div class="mt-3 flex items-center justify-between">
                    <button type="button" id="copy-characters"
                        onclick="copyCharacters()"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium text-gray-600 ring-1 ring-gray-200 transition hover:text-seal hover:ring-seal/40 {{ $myCharacters === '' ? 'hidden' : '' }}">
                        复制 · Copy
                    </button>
                    <a href="{{ url('/characters') }}"
                        class="text-sm text-gray-500 underline decoration-gray-300 underline-offset-4 transition hover:text-seal">
                        修改 · Edit my characters →
                    </a>
                </div>
            </section>
 
 


            {{-- ── History: recent texts ────────────────────── --}}
            <section class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <div class="mb-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="grid h-6 w-6 place-items-center rounded-md bg-seal font-serifsc text-sm text-white">读</span>
                        <h2 class="font-serifsc text-lg font-bold text-gray-900">Your History</h2>
                    </div>
                    <a href="{{ route('history') }}" class="text-sm text-gray-500 underline decoration-gray-300 underline-offset-4 transition hover:text-seal">
                        全部 · All texts →
                    </a>
                </div>

                @if ($recentTexts->isEmpty())
                    <p class="text-sm leading-relaxed text-gray-500">
                        No texts yet — generate your first story and it'll appear here.
                    </p>
                @else
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($recentTexts as $t)
                            <a href="{{ url('/texts/' . $t['id']) }}"
                               class="group rounded-xl p-4 ring-1 ring-gray-200 transition hover:-translate-y-0.5 hover:shadow-md">
                                <p class="font-serifsc text-lg leading-relaxed text-gray-900 group-hover:text-seal-deep">
                                    {{ $t['snippet'] }}
                                </p>
                                <p class="mt-2 text-xs text-gray-500">{{ $t['date'] }} · {{ $t['chars'] }} 字</p>
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>

        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <script>
        // greeting from the browser's clock
        const h = new Date().getHours();
        document.getElementById('greeting').textContent =
            h < 12 ? '早上好 · Morning' : h < 18 ? '下午好 · Afternoon' : '晚上好 · Evening';

        // reading-activity chart
        new Chart(document.getElementById('activity'), {
            type: 'bar',
            data: {
                labels: @json($activityLabels),
                datasets: [{
                    data: @json($activityCounts),
                    backgroundColor: '#c0392b',
                    borderRadius: 4,
                    barPercentage: 0.7,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: c => `${c.parsed.y} texts read` } },
                },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0, color: '#7a7266' }, grid: { color: 'rgba(0,0,0,.05)' } },
                    x: { display: false, grid: { display: false } },
                },
            },
        });
    </script>
</x-app-layout>