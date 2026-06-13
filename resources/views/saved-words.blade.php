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
                    <a href="{{ url('/generate') }}"
                       onclick="document.getElementById('loading-modal').classList.remove('hidden')"
                       class="mt-5 inline-flex items-center rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                        读一个故事 · Read a text
                    </a>
                </div>

            @else

                <p class="mb-5 px-1 text-sm text-gray-500">
                    Words you've tapped while reading.
                    <span class="text-gray-400">Click a card to reopen the text it came from.</span>
                </p>

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

                                {{-- Card: links to the source text when we know it --}}
                                <component
                                    :is="w.source_id ? 'a' : 'div'"
                                    x-bind="w.source_id ? { href: '/texts/' + w.source_id, target: '_blank', rel: 'noopener' } : {}"
                                    class="flex items-start gap-4 rounded-2xl bg-white px-5 py-5 pr-12 shadow-sm ring-1 ring-gray-200 transition sm:gap-6 sm:px-7"
                                    :class="w.source_id ? 'cursor-pointer hover:-translate-y-0.5 hover:shadow hover:ring-indigo-300' : ''"
                                >
                                    {{-- big character --}}
                                    <span class="font-serifsc text-5xl leading-none text-gray-900 shrink-0 sm:text-6xl" x-text="w.word"></span>

                                    {{-- pinyin (red, top) + english (under) --}}
                                    <div class="min-w-0 flex-1 pt-1">
                                        <span class="block text-base font-medium text-seal sm:text-lg" x-text="w.pinyin"></span>
                                        <span class="mt-1 block text-sm leading-snug text-gray-600 sm:text-base" x-text="w.english"></span>
                                    </div>

                                    {{-- open-source hint --}}
                                    <span x-show="w.source_id"
                                        class="pointer-events-none absolute bottom-3 right-4 text-[11px] text-gray-300 opacity-0 transition group-hover:opacity-100">
                                        打开课文 · open text ↗
                                    </span>
                                </component>

                                {{-- × delete, pinned top-right, above the card link --}}
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