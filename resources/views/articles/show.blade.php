<x-public-layout :title="$article->title">
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ $article->title }}</h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-3xl px-2 sm:px-6 lg:px-8">

            {{-- Breadcrumb + admin controls --}}
            <div class="no-print mb-4 flex items-center justify-between px-2">
                <a href="{{ route('articles.index') }}" class="text-sm text-gray-500 hover:text-seal">← All articles</a>

                @if (auth()->id() === 1)
                    <div class="flex items-center gap-3">
                        @unless ($article->is_published)
                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-700">Draft</span>
                        @endunless
                        <a href="{{ route('articles.edit', $article) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">Edit</a>
                        <form method="POST" action="{{ route('articles.destroy', $article) }}"
                              onsubmit="return confirm('Delete this article?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-sm font-semibold text-seal hover:text-seal-deep">Delete</button>
                        </form>
                    </div>
                @endif
            </div>

            @if (session('status'))
                <div class="no-print mb-4 rounded-lg bg-green-50 px-4 py-2 text-sm text-green-700 ring-1 ring-green-100">
                    {{ session('status') }}
                </div>
            @endif

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">

                {{-- Title + meta --}}
                <div class="px-8 pt-10 sm:px-12 sm:pt-14">
                    <div class="mb-3 flex items-center gap-2">
                        @if ($article->hsk_level)
                            <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-indigo-600">HSK {{ $article->hsk_level }}</span>
                        @endif
                        @if ($article->publication_date)
                            <span class="text-[11px] text-gray-400">{{ $article->publication_date->format('F j, Y') }}</span>
                        @endif
                    </div>
                    <h1 class="font-serifsc text-4xl leading-snug text-gray-900">{{ $article->title }}</h1>
                    @if ($article->summary)
                        <p class="mt-3 text-base leading-relaxed text-gray-500">{{ $article->summary }}</p>
                    @endif
                </div>

                {{-- Reading area. definitions = the JSON stored on the article, passed
                     through as-is. textId=null => no save-word for guests. --}}
                <x-chinese-passage-article
                    :story="$article->body"
                    :definitions="$article->definitions ?? []"
                    :chinese="$article->body"
                    :english="$article->english ?? ''"
                    :textId="null"/>

                {{-- CTA for guests/free --}}
                <div class="no-print border-t border-gray-100 bg-gray-50 px-8 py-6 text-center sm:px-12">
                    <a href="{{ url('/') }}"
                       class="inline-flex items-center rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                        Read texts made from only the characters you know →
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-public-layout>
