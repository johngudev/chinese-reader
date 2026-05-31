<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            你的故事 · Your Story
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            @if(!empty($story))

                <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">

                    {{-- Reading area --}}


                    
                    <x-chinese-passage-article :story="$story" />


                    {{-- Footer / actions --}}
                    <div class="flex items-center justify-between border-t border-gray-100 bg-gray-50 px-8 py-4 sm:px-12">
                        <span class="text-sm text-gray-500">
                            {{ preg_match_all('/\p{Han}/u', $story) }} Chinese characters are in this text.
                        </span>
                        <a href="{{ url('/generate') }}"
                            onclick="document.getElementById('loading-modal').classList.remove('hidden')"
                            class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            生成新故事 · Generate another text
                        </a>
                    </div>

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
                        <a href="{{ url('/generate') }}"
                        onclick="document.getElementById('loading-modal').classList.remove('hidden')"
                        class="mt-2 inline-flex items-center rounded-lg bg-white px-6 py-2.5 font-semibold text-indigo-600 shadow-sm transition hover:-translate-y-0.5 hover:bg-gray-100">
                            生成新故事 · Generate a text
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>