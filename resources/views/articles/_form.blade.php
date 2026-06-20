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

    {{-- Definitions editor: one token per line, formatted as
             word | pinyin | definition
         Pinyin/definition are optional (punctuation, English words can be the
         bare word). Each line becomes a token; lines with no pinyin/definition
         store an empty entries array. Seeds from old() on validation error,
         otherwise rebuilt from the saved token array. --}}
    @php
        $definitionsText = old('definitions_text');
        if ($definitionsText === null && !empty($article->definitions)) {
            $lines = [];
            foreach ($article->definitions as $token) {
                $word    = $token['word'] ?? '';
                $entries = $token['entries'] ?? [];
                if (!empty($entries)) {
                    $lines[] = $word . ' | ' . ($entries[0]['pinyin'] ?? '') . ' | ' . ($entries[0]['english'] ?? '');
                } else {
                    $lines[] = $word;
                }
            }
            $definitionsText = implode("\n", $lines);
        }
        $definitionsText = $definitionsText ?? '';
    @endphp

    <div>
        <x-input-label for="definitions_text" value="Definitions (one per line: 词 | pīnyīn | definition)" />
        <textarea id="definitions_text" name="definitions_text" rows="14"
                  class="mt-1 block w-full rounded-md border-gray-300 font-mono text-sm leading-relaxed shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  placeholder="和 | hé | and&#10;Iran&#10;说 | shuō | to say&#10;，">{{ $definitionsText }}</textarea>
        <p class="mt-1 text-xs text-gray-400">Use “|” to separate word, pinyin, and definition. Punctuation and English words can be just the word with no pinyin/definition.</p>
    </div>

    {{-- Actions --}}
    <div class="flex items-center gap-4 pt-2">
        <x-primary-button>{{ $submitLabel ?? 'Save article' }}</x-primary-button>
        <a href="{{ route('articles.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
    </div>
</form>
