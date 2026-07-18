<x-public-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            你的故事 · Your Story
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl px-2 sm:px-6 lg:px-8">
            {{-- STATE 2: warning banner on the lead-up (1–3 left) --}}
            @if(($remaining ?? null) !== null && $remaining >= 0 && $remaining <= 2)
                <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-center text-sm font-medium text-amber-800">
                    @if($remaining === 0)
                        No more generations left today.  <a href="{{ route('premium') }}" class="underline">Sign up for Premium</a> to generate unlimited texts.
                    @else
                        {{ $remaining }} more {{ $remaining === 1 ? 'generation' : 'generations' }} left today
                    @endif
                </div>
            @endif

            {{-- STATE 3: locked — no story, countdown to reset + upgrade --}}
            @if($locked ?? false)
                <div class="mt-6 rounded-2xl bg-white px-6 py-10 text-center shadow-sm ring-1 ring-gray-100">
                    <span class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-stone-100 font-serifsc text-2xl text-stone-500">锁</span>
                    <p class="mt-4 text-lg font-semibold text-gray-900">No more generations today · Limit 5 generations per day</p>
                    <p class="mt-1 text-sm text-gray-500">
                        Resets in <span id="countdown" class="font-mono tabular-nums text-gray-700">--:--:--</span>
                    </p>
                    <a href="{{ route('premium') }}"
                        class="mt-6 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-indigo-500">
                        升级 · Upgrade for unlimited generations
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>

                <script>
                    (function () {
                        const resetsAt = new Date(@json($resetsAt ?? null)).getTime();
                        const el = document.getElementById('countdown');
                        const pad = n => String(n).padStart(2, '0');
                        (function tick() {
                            const d = resetsAt - Date.now();
                            if (d <= 0) { el.textContent = '0:00:00'; return; }
                            const h = Math.floor(d / 3.6e6),
                                  m = Math.floor(d % 3.6e6 / 6e4),
                                  s = Math.floor(d % 6e4 / 1e3);
                            el.textContent = h + ':' + pad(m) + ':' + pad(s);
                            setTimeout(tick, 1000);
                        })();
                    })();
                </script>
            @elseif(!empty($story))

                <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">

                    {{-- Reading area --}}

                    @if(empty($definitions))
                        @php
                            $definitions = [];
                        @endphp
                    @endif
                    
                    <x-chinese-passage-article :story="$story" :definitions="$definitions" :chinese="$chinese" :english="$english" :textId="$textId" :savedWords="$savedWords" :is-owner="$isOwner ?? false"/>

                    @auth
                    @if (auth()->user()->isPremium())
                        {{-- Footer / actions for Premium --}}
                        <div class="no-print flex flex-col items-center gap-4 border-t border-gray-100 bg-gray-50 px-8 py-6 sm:px-12">
                            <span class="text-sm text-gray-500">
                                {{ preg_match_all('/\p{Han}/u', $story) }} Chinese characters are in this text.
                            </span>

                            <form method="POST" action="{{ url('/generate') }}"
                                onsubmit="document.getElementById('loading-modal').classList.remove('hidden')"
                                class="flex w-full flex-col items-center gap-3">
                                @csrf

                                <x-text-type-fieldset theme="light" />

                                <x-advanced-options theme="light" />

                                <button type="submit"
                                    class="inline-flex w-72 max-w-full items-center justify-center rounded-lg bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    生成新故事 · Generate another text
                                </button>
                            </form>
                        </div>
                    @else
                    {{-- Footer / actions for Free versino--}}
                    <div class="no-print flex items-center justify-center sm:justify-between border-t border-gray-100 bg-gray-50 px-8 py-4 sm:px-12">
                        <span class="text-sm text-gray-500 hidden sm:inline">
                            {{ preg_match_all('/\p{Han}/u', $story) }} Chinese characters are in this text.
                        </span>
                        <form method="POST" action="{{ url('/generate') }}"
                            onsubmit="document.getElementById('loading-modal').classList.remove('hidden')">
                            @csrf
                            <button type="submit"
                                class="max-w-sm inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                生成新故事 · Generate another text
                            </button>
                        </form>
                    </div>
                    @endif
                    @else
                    {{-- Footer / CTA for guests --}}
                    <div class="no-print flex flex-col items-center gap-3 border-t border-gray-100 bg-gray-50 px-8 py-6 text-center sm:px-12">
                        <p class="text-sm text-gray-600">
                            想读用你认识的字写的故事？ · Want texts written with only the characters <span class="font-semibold">you</span> know?
                        </p>
                        <a href="{{ route('register') }}"
                            class="inline-flex items-center rounded-lg bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-indigo-500">
                            免费注册 · Sign up free
                        </a>
                    </div>
                    @endauth

                </div>
            @else
                <div class="relative mt-6 overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 to-indigo-500 px-6 py-10 text-center text-white shadow-sm">
                    {{-- Decorative watermark --}}
                    <div aria-hidden="true"
                        class="pointer-events-none absolute -right-4 -top-6 select-none font-serifsc text-[9rem] leading-none text-white/10">
                        读
                    </div>

                    <div class="relative flex flex-col items-center gap-3">
                        <p class="text-lg font-semibold">读一个故事 · Ready to read?</p>
                        <p class="max-w-sm text-sm text-white/80">
                            We'll write you a text using only the characters you know.
                        </p>

                        <form method="POST" action="{{ url('/generate') }}"
                            onsubmit="document.getElementById('loading-modal').classList.remove('hidden')"
                            class="mt-2 flex flex-col items-center gap-3">
                            @csrf

                            @if (auth()->check() && auth()->user()->isPremium())
                                <x-text-type-fieldset theme="dark" />

                                <x-advanced-options theme="dark" />
                            @endif

                            <button type="submit"
                                class="inline-flex w-72 max-w-full items-center justify-center rounded-lg bg-white px-6 py-2.5 font-semibold text-indigo-600 shadow-sm transition hover:-translate-y-0.5 hover:bg-gray-100">
                                生成新故事 · Generate a text
                            </button>
                        </form>
                    </div>
                    
                </div>
            @endif

            @if(!empty($story))
            @if(auth()->check() && ($isOwner ?? false))
            <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('savedWords', ({ initial }) => ({
                    words: initial ?? [],
                    add(word) {
                        if (this.words.some(w => w.id === word.id)) return;
                        this.words.push(word);
                    },
                    async remove(word) {
                        this.words = this.words.filter(w => w.id !== word.id);
                        try {
                            await fetch('/saved-words/' + word.id, {
                                method: 'DELETE',
                                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                            });
                        } catch { this.words.push(word); }
                    },
                }));
            });
            </script>

        <div
            x-data="savedWords({ initial: @js($savedWords) })"
            @word-saved.window="add($event.detail)"
            class="no-print mx-auto max-w-3xl bg-gray-50 px-8 py-6 sm:px-12"
        >
            <h3 class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500">生词 · Words you looked up</h3>
            <p x-show="words.length === 0" class="mt-2 text-sm text-gray-400">Tap a word above to add it here.</p>

            <ul class="mt-3 flex flex-col gap-2">
                <template x-for="w in words" :key="w.id">
                    <li class="relative flex items-start gap-3 rounded-xl bg-white px-4 py-3 pr-9 shadow-sm ring-1 ring-gray-200">

                        {{-- big character --}}
                        <span class="font-serifsc text-3xl leading-none text-gray-900 shrink-0" x-text="w.word"></span>

                        {{-- pinyin + definition stacked beside it --}}
                        <div class="min-w-0 flex-1">
                            <span class="block text-xs text-seal" x-text="w.pinyin"></span>
                            <span class="mt-0.5 block text-xs leading-snug text-gray-500 line-clamp-2" x-text="w.english"></span>
                        </div>

                        {{-- × pinned top-right --}}
                        <button @click="remove(w)"
                            class="absolute right-2 top-2 text-gray-300 transition hover:text-seal"
                            aria-label="Remove">&times;</button>
                    </li>
                </template>
            </ul>
        </div>
        @endif
        @endif

            {{-- Share this text — under everything, the last element on the page --}}
            @if(!empty($story) && !empty($textId))
                <x-share-text :chinese="$chinese" :text-id="$textId" />
            @endif

        </div>
        

    </div>
</x-public-layout>