@if ($errors->any())
    <div class="mb-6 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 ring-1 ring-red-100">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if (($method ?? 'POST') !== 'POST')
        @method($method)
    @endif

    {{-- Title --}}
    <div>
        <x-input-label for="title" value="Title (in English)" />
        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full"
                      :value="old('title', $article->title)" required autofocus />
    </div>

    {{-- Slug --}}
    <div>
        <x-input-label for="slug" value="Slug (URL — optional, auto-generated if blank)" />
        <x-text-input id="slug" name="slug" type="text" class="mt-1 block w-full"
                      :value="old('slug', $article->slug)" placeholder="e.g. beijing-weather-report" />
        <p class="mt-1 text-xs text-gray-400">Tip: use ASCII so the URL stays clean (Chinese titles fall back to “article”).</p>
    </div>

    {{-- Summary --}}
    <div>
        <x-input-label for="summary" value="Summary / excerpt (shown in the list + meta description)" />
        <textarea id="summary" name="summary" rows="2"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('summary', $article->summary) }}</textarea>
    </div>

    {{-- Body --}}
    <div>
        <x-input-label for="body" value="Body (Chinese article)" />
        <textarea id="body" name="body" rows="12" required
                  class="mt-1 block w-full rounded-md border-gray-300 font-serifsc text-lg leading-loose shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  style="font-family: 'Noto Serif SC', serif;">{{ old('body', $article->body) }}</textarea>
    </div>

    {{-- English --}}
    <div>
        <x-input-label for="english" value="English translation / notes (optional)" />
        <textarea id="english" name="english" rows="6"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('english', $article->english) }}</textarea>
    </div>

    {{-- HSK + date + published --}}
    <div class="grid gap-6 sm:grid-cols-3">
        <div>
            <x-input-label for="hsk_level" value="HSK level" />
            <select id="hsk_level" name="hsk_level"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">—</option>
                @foreach (range(1, 9) as $level)
                    <option value="{{ $level }}" @selected((string) old('hsk_level', $article->hsk_level) === (string) $level)>HSK {{ $level }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <x-input-label for="publication_date" value="Publication date" />
            <x-text-input id="publication_date" name="publication_date" type="date" class="mt-1 block w-full"
                          :value="old('publication_date', optional($article->publication_date)->format('Y-m-d'))" />
        </div>

        <div class="flex items-end pb-1">
            <label class="inline-flex items-center gap-2">
                <input type="hidden" name="is_published" value="0">
                <input type="checkbox" name="is_published" value="1"
                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                       @checked(old('is_published', $article->is_published)) />
                <span class="text-sm text-gray-700">Published</span>
            </label>
        </div>
    </div>

    {{-- Glossary editor (Alpine).
         Seeds from old() on validation error, otherwise from the saved
         definitions token array (word + first entry's pinyin/english). --}}
    @php
        $glossRows = [];
        if (old('gloss_word')) {
            foreach (old('gloss_word') as $i => $w) {
                $glossRows[] = [
                    'word'    => $w,
                    'pinyin'  => old('gloss_pinyin')[$i] ?? '',
                    'english' => old('gloss_english')[$i] ?? '',
                ];
            }
        } elseif (!empty($article->definitions)) {
            foreach ($article->definitions as $token) {
                $entries = $token['entries'] ?? [];
                $glossRows[] = [
                    'word'    => $token['word'] ?? '',
                    'pinyin'  => $entries[0]['pinyin'] ?? '',
                    'english' => $entries[0]['english'] ?? '',
                ];
            }
        }
    @endphp

    <div x-data="{ rows: @js($glossRows) }" class="rounded-xl bg-gray-50 p-5 ring-1 ring-gray-200">
        <div class="mb-3 flex items-center justify-between">
            <h3 class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500">生词 · Glossary (manual)</h3>
            <button type="button" @click="rows.push({ word: '', pinyin: '', english: '' })"
                    class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">+ Add word</button>
        </div>

        <p x-show="rows.length === 0" class="py-4 text-sm text-gray-400">No glossary words yet. Add the key vocabulary you want listed beneath the article.</p>

        <div class="flex flex-col gap-2">
            <template x-for="(row, i) in rows" :key="i">
                <div class="grid grid-cols-12 items-start gap-2">
                    <input type="text" name="gloss_word[]" x-model="row.word" placeholder="词"
                           class="col-span-3 rounded-md border-gray-300 font-serifsc text-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           style="font-family: 'Noto Serif SC', serif;">
                    <input type="text" name="gloss_pinyin[]" x-model="row.pinyin" placeholder="cí"
                           class="col-span-3 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <input type="text" name="gloss_english[]" x-model="row.english" placeholder="word; term"
                           class="col-span-5 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <button type="button" @click="rows.splice(i, 1)"
                            class="col-span-1 justify-self-center pt-2 text-gray-300 hover:text-seal" aria-label="Remove row">&times;</button>
                </div>
            </template>
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex items-center gap-4 pt-2">
        <x-primary-button>{{ $submitLabel ?? 'Save article' }}</x-primary-button>
        <a href="{{ route('articles.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
    </div>
</form>
