<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">报纸文章 · Newspaper Articles</h2>
    </x-slot>

    <div class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">

        <header class="mb-8">
            <h1 class="font-serifsc text-3xl text-gray-900">报纸文章 · Newspaper Articles</h1>
            <p class="mt-2 max-w-2xl text-sm text-gray-500">
                Short articles in graded Chinese. Free to read — no account needed.
            </p>
        </header>

        {{-- HSK filter --}}
        <div class="no-print mb-8 flex flex-wrap items-center gap-2">
            <a href="{{ route('articles.index') }}"
               class="rounded-full px-3 py-1 text-xs font-semibold {{ $hsk ? 'bg-white text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50' : 'bg-seal text-white' }}">
                All
            </a>
            @foreach (range(1, 6) as $level)
                <a href="{{ route('articles.index', ['hsk' => $level]) }}"
                   class="rounded-full px-3 py-1 text-xs font-semibold {{ (int) $hsk === $level ? 'bg-seal text-white' : 'bg-white text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50' }}">
                    HSK {{ $level }}
                </a>
            @endforeach
        </div>

        @if ($articles->isEmpty())
            <div class="rounded-2xl bg-white px-8 py-16 text-center text-gray-400 shadow-sm ring-1 ring-gray-100">
                No articles yet{{ $hsk ? ' for HSK '.$hsk : '' }}.
            </div>
        @else
            <div class="grid gap-5 sm:grid-cols-2">
                @foreach ($articles as $article)
                    <a href="{{ route('articles.show', $article) }}"
                       class="group flex flex-col rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100 transition hover:-translate-y-0.5 hover:shadow-md">
                        <div class="mb-3 flex items-center gap-2">
                            @if ($article->hsk_level)
                                <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-indigo-600">HSK {{ $article->hsk_level }}</span>
                            @endif
                            @if ($article->publication_date)
                                <span class="text-[11px] text-gray-400">{{ $article->publication_date->format('M j, Y') }}</span>
                            @endif
                            @unless ($article->is_published)
                                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-700">Draft</span>
                            @endunless
                        </div>

                        <h2 class="font-serifsc text-2xl text-gray-900 group-hover:text-seal">{{ $article->title }}</h2>

                        @if ($article->summary)
                            <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-gray-500">{{ $article->summary }}</p>
                        @endif

                        <span class="mt-4 text-xs font-semibold text-seal">阅读 · Read →</span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
