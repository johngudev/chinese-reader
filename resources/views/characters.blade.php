<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            我的汉字 · My Characters
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-2xl bg-white p-8 shadow-sm ring-1 ring-gray-100">

                @if (session('status'))
                    <div class="mb-6 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ url('/characters') }}">
                    @csrf

                    <label for="characters" class="block text-sm font-medium text-gray-700">
                        Paste the characters you know
                    </label>
                    <p class="mt-1 text-sm text-gray-500">
                        Spaces, punctuation, and non-Chinese text are ignored — only Chinese characters are saved. Saving replaces your current list.
                    </p>

                    <textarea name="characters" id="characters" rows="10"
                        class="mt-3 block w-full rounded-lg border-gray-300 font-serifsc text-lg leading-relaxed focus:border-seal focus:ring-seal"
                        placeholder="你好我是中国人…">{{ implode(' ', $characters) }}</textarea>

                    <div class="mt-4 flex items-center justify-between">
                        <span class="text-sm text-gray-500">
                            Currently saved: {{ count($characters) }} characters
                        </span>
                        <button type="submit"
                            class="inline-flex items-center rounded-full bg-seal px-6 py-2.5 font-semibold text-white shadow-sm transition hover:bg-seal-deep">
                            保存 · Save
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>