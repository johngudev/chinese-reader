<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            我的汉字 · My Characters
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl px-2 sm:px-6 lg:px-8">
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

                    {{-- Preset character sets --}}
                    <div class="mt-5 grid grid-cols-1 gap-2.5 sm:grid-cols-2">
 
                        {{-- Custom — full width, the user's own saved list --}}
                        <label class="relative block cursor-pointer sm:col-span-2">
                            <input type="radio" name="vocab_set" value="custom" checked
                                onclick="fillCharactersTextBox(this)"
                                data-characters-list-name="custom"
                                class="peer absolute left-3.5 top-1/2 z-10 h-4 w-4 -translate-y-1/2 accent-seal">
                            <div class="flex items-center gap-3 rounded-lg border border-line bg-white py-3 pl-10 pr-3.5 text-ink transition hover:border-ink-soft peer-checked:border-seal peer-checked:bg-seal/5 peer-checked:text-seal-deep">
                                <span class="text-sm leading-tight">
                                    <span class="font-semibold">我的字 · My custom list</span>
                                    <span class="ml-1 text-gray-500">— the characters you've saved</span>
                                </span>
                                <span class="ml-auto font-serifsc text-xs">{{ count($characters) }} 字</span>
                            </div>
                        </label>
 
                        {{-- Presets --}}
                        @foreach ($vocabLists as $i => $list)
                            <label class="relative block cursor-pointer">
                                <input type="radio" name="vocab_set" value="{{ $i }}"
                                    onclick="fillCharactersTextBox(this)"
                                    data-characters-list-name="{{ $list['vocab_list_name'] }}"
                                    class="peer absolute left-3.5 top-1/2 z-10 h-4 w-4 -translate-y-1/2 accent-seal">
                                <div class="flex items-center gap-3 rounded-lg border border-line bg-white py-3 pl-10 pr-3.5 text-ink transition hover:border-ink-soft peer-checked:border-seal peer-checked:bg-seal/5 peer-checked:text-seal-deep">
                                    <span class="text-sm leading-tight">{{ $list['vocab_list_name'] }}</span>
                                    <span class="ml-auto font-serifsc text-xs">{{ count($list['characters']) }} 字</span>
                                </div>
                            </label>
                        @endforeach
                    </div>


                    <textarea name="characters" id="characters" rows="10"
                        class="mt-3 block w-full rounded-lg border-gray-300 font-serifsc text-lg leading-relaxed focus:border-seal focus:ring-seal"
                        placeholder="你好我是中国人…">{{ implode(' ', $characters) }}</textarea>

                    <div class="my-4 flex items-center justify-between">
                        <span class="text-sm text-gray-500">
                            Currently saved: {{ count($characters) }} characters
                        </span>

                        <button type="submit"
                            onclick="document.getElementById('loading-modal').classList.remove('hidden')"
                            class="inline-flex items-center rounded-lg bg-seal px-6 py-2.5 font-semibold text-white shadow-sm transition hover:bg-seal-deep">
                            <span class="hidden md:inline">保存 · </span>Save
                        </button>
                    </div>
                </form>

            </div>

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
                    <form method="POST" action="{{ url('/generate') }}">
                        @csrf
                        <button type="submit"
                            onclick="document.getElementById('loading-modal').classList.remove('hidden')"
                            class="mt-2 inline-flex items-center rounded-lg bg-white px-6 py-2.5 font-semibold text-indigo-600 shadow-sm transition hover:-translate-y-0.5 hover:bg-gray-100">
                            生成新故事 · Generate a text
                        </button>
                    </form>
                </div>
            </div>
            
        </div>
    </div>

    <script>
        const characters_lists = @json($vocabLists);
        const custom_characters = @json(implode(' ', $characters));   // the user's saved list, frozen at page load
 
        function fillCharactersTextBox(input) {
            const name = input.getAttribute('data-characters-list-name');
            const box  = document.getElementById('characters');
 
            if (name === 'custom') {
                box.value = custom_characters;
            } else {
                const characters = characters_lists.find(l => l.vocab_list_name === name)?.characters ?? [];
                box.value = characters.join(' ');
            }
            updateCount();
        }
 
        function updateCount() {
            const count = (document.getElementById('characters').value.match(/\p{Script=Han}/gu) ?? []).length;
            document.getElementById('characters-textbox-count').textContent = `Currently using: ${count} characters`;
        }
 
        // Typing in the box = it's custom now; flip the radio back
        document.getElementById('characters').addEventListener('input', () => {
            document.querySelector('input[name="vocab_set"][value="custom"]').checked = true;
            updateCount();
        });
    </script>
</x-app-layout>