<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            生词 · Saved Words
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl px-2 sm:px-6 lg:px-8">

            @if ($words->isEmpty())

                {{-- Empty state --}}
                <div class="overflow-hidden rounded-2xl border-2 border-dashed border-gray-200 bg-white/60 p-10 text-center">
                    <p class="font-serifsc text-4xl text-gray-300">还没有生词</p>
                    <p class="mt-3 text-sm text-gray-500">
                        Tap any word while reading a text, and it'll be saved here for review.
                    </p>
                    <a href="{{ url('/story') }}"
                       class="mt-5 inline-flex items-center rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                        读一个故事 · Read a text
                    </a>
                </div>

            @else

                <p class="mb-5 px-1 text-sm text-gray-500">
                    Words you've tapped while reading.
                    <span class="text-gray-400">Click a card to reopen the text it came from.</span>
                </p>

                <div class="relative">
                <div @class(['pointer-events-none select-none blur-sm' => $locked ?? false])>
                <div
                    x-data="savedWordsList({ initial: @js($words->map(fn ($w) => [
                        'id'        => $w->id,
                        'word'      => $w->word,
                        'pinyin'    => $w->pinyin,
                        'english'   => $w->english,
                        'source_id' => $w->generated_text_id,
                    ])) })"
                >
                    <ul class="flex flex-col gap-3">
                        <template x-for="w in words" :key="w.id">
                            <li class="group relative">

                                <a :href="w.source_id ? '/texts/' + w.source_id : null"
                                :target="w.source_id ? '_blank' : null"
                                rel="noopener"
                                class="flex items-start gap-4 rounded-2xl bg-white px-5 py-5 pr-12 shadow-sm ring-1 ring-gray-200 transition sm:gap-6 sm:px-7"
                                :class="w.source_id ? 'cursor-pointer hover:-translate-y-0.5 hover:shadow hover:ring-indigo-300' : 'cursor-default'">

                                    {{-- big character --}}
                                    <span class="font-serifsc text-5xl leading-none text-gray-900 shrink-0 sm:text-6xl" x-text="w.word"></span>

                                    {{-- pinyin (red) + english --}}
                                    <div class="min-w-0 flex-1 pt-1">
                                        <span class="block text-base font-medium text-seal sm:text-lg" x-text="w.pinyin"></span>
                                        <span class="mt-1 block text-sm leading-snug text-gray-600 sm:text-base" x-text="w.english"></span>
                                    </div>

                                    {{-- open-source hint, only when linkable --}}
                                    <span x-show="w.source_id"
                                        class="pointer-events-none absolute bottom-3 right-4 text-[11px] text-gray-300 opacity-0 transition group-hover:opacity-100">
                                        打开课文 · open text ↗
                                    </span>
                                </a>

                                {{-- × delete --}}
                                <button @click="remove(w)"
                                    class="absolute right-3 top-3 z-10 grid h-7 w-7 place-items-center rounded-full text-gray-300 transition hover:bg-seal/10 hover:text-seal"
                                    aria-label="Remove">
                                    <span class="text-xl leading-none">&times;</span>
                                </button>
                            </li>
                        </template>
                    </ul>

                    {{-- if the user deletes everything on the page --}}
                    <p x-show="words.length === 0" class="mt-6 text-center text-sm text-gray-400">
                        No more saved words on this page.
                    </p>
                </div>



                {{-- Pagination --}}
                <div class="mt-8">
                    {{ $words->links() }}
                </div>
                </div>

                @if($locked ?? false)
                    <div class="absolute inset-0 z-20 grid place-items-center rounded-2xl bg-white/50 backdrop-blur-[2px]">
                        <div class="mx-4 max-w-sm rounded-2xl bg-white px-6 py-8 text-center shadow-lg ring-1 ring-gray-100">
                            <span class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-stone-100 font-serifsc text-2xl text-stone-500">锁</span>
                            <p class="mt-4 text-lg font-semibold text-gray-900">See all your saved words</p>
                            <p class="mt-1 text-sm text-gray-500">Sign up for premium to see all your saved words.</p>
                            <a href="{{ route('premium') }}"
                               class="mt-5 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-indigo-500">
                                升级 · Upgrade to Premium to see all your saved words
                                <span aria-hidden="true">&rarr;</span>
                            </a>
                        </div>
                    </div>
                @endif
                </div>

            @endif

        </div>
    </div>

    @auth
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('savedWordsList', ({ initial }) => ({
                words: initial ?? [],
                async remove(word) {
                    const i = this.words.findIndex(w => w.id === word.id);
                    if (i === -1) return;
                    const [removed] = this.words.splice(i, 1);          // optimistic
                    try {
                        const res = await fetch('/saved-words/' + word.id, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                        });
                        if (!res.ok) throw new Error();
                    } catch {
                        this.words.splice(i, 0, removed);               // rollback on failure
                    }
                },
            }));
        });
    </script>
    @endauth
</x-app-layout>