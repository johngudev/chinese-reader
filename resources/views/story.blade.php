<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            你的故事 · Your Story
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">

                {{-- Reading area --}}
                <article class="px-8 py-10 sm:px-12 sm:py-14">
                    <p class="whitespace-pre-line text-2xl leading-loose tracking-wide text-gray-900"
                       style="font-family: 'Noto Serif SC', 'Songti SC', serif;">
                        {{ $story }}
                    </p>
                </article>

                {{-- Footer / actions --}}
                <div class="flex items-center justify-between border-t border-gray-100 bg-gray-50 px-8 py-4 sm:px-12">
                    <span class="text-sm text-gray-500">
                        {{ mb_strlen(preg_replace('/\s+/u', '', $story)) }} characters
                    </span>
                    <a href="{{ url('/generate') }}"
                       class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        生成新故事 · Generate another
                    </a>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>