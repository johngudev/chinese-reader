<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            我的故事 · My Texts
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl px-2 sm:px-6 lg:px-8">

            @if ($texts->isEmpty())

                {{-- Empty state --}}
                <div class="overflow-hidden rounded-2xl bg-white p-10 text-center shadow-sm ring-1 ring-gray-100">
                    <p class="font-serifsc text-3xl text-gray-300">还没有故事</p>
                    <p class="mt-2 text-sm text-gray-500">
                        You haven't read any texts yet — generate your first one below.
                    </p>
                </div>

            @else

                <ul class="space-y-4">
                    @foreach ($texts as $text)
                        @php
                            // Split stored story into Chinese and English parts
                            [$chinese, $english] = array_pad(explode('<hr>', $text->generated_text ?? ''), 2, '');
                            $chinese = trim(strip_tags($chinese));

                            // Chinese: cut by characters (every char is a "word")
                            $chineseSnippet = mb_substr($chinese, 0, 24) . (mb_strlen($chinese) > 24 ? '…' : '');

                            // English: cut by words, never mid-word
                            $englishSnippet = \Illuminate\Support\Str::words($english, 10, '…');
                            $charCount = preg_match_all('/\p{Han}/u', $chinese);
                        @endphp

                        <li>
                            <a href="{{ url('/texts/' . $text->id) }}"
                                class="block overflow-hidden rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100 transition hover:ring-indigo-300 hover:shadow sm:p-7">

                                <p class="font-serifsc text-xl leading-relaxed text-gray-900">
                                    {{ $chineseSnippet }}
                                </p>
                                <p class="mt-1 text-sm leading-relaxed text-gray-500">
                                    {{ $englishSnippet }}
                                </p>

                                <div class="mt-3 flex items-center justify-between">
                                    <span class="text-sm text-gray-500">
                                        {{ $charCount }} 字 · {{ $text->created_at->diffForHumans() }}
                                    </span>
                                    <span class="text-sm font-medium text-indigo-600">
                                        再读一次 · Read again →
                                    </span>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>

            @endif

            {{-- Generate CTA (same card as characters page) --}}
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
                    <a href="{{ url('/generate') }}"
                        onclick="document.getElementById('loading-modal').classList.remove('hidden')"
                        class="mt-2 inline-flex items-center rounded-lg bg-white px-6 py-2.5 font-semibold text-indigo-600 shadow-sm transition hover:-translate-y-0.5 hover:bg-gray-100">
                        生成新故事 · Generate a text
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>