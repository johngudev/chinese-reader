<?php

namespace App\Http\Controllers;

use App\Models\NewspaperArticle;
use Illuminate\Http\Request;

class NewspaperArticleController extends Controller
{
    /**
     * Admin-only gate (matches the app's existing convention:
     * abort_unless(auth()->id() === 1, 403)). Applied to the
     * create/store/edit/update/destroy actions only.
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless(auth()->id() === 1, 403);
            return $next($request);
        })->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * PUBLIC: list of published articles. Optional ?hsk= filter.
     */
    public function index(Request $request)
    {
        $hsk = $request->integer('hsk') ?: null;

        $articles = NewspaperArticle::published()
            ->when($hsk, fn ($q) => $q->where('hsk_level', $hsk))
            ->get();

        return view('articles.index', [
            'articles' => $articles,
            'hsk'      => $hsk,
        ]);
    }

    /**
     * PUBLIC: read one article. Guests and free users included.
     * Drafts are only viewable by the admin.
     */
    public function show(NewspaperArticle $newspaperArticle)
    {
        abort_unless($newspaperArticle->is_published || auth()->id() === 1, 404);

        return view('articles.show', ['article' => $newspaperArticle]);
    }

    /**
     * ADMIN: new-article form.
     */
    public function create()
    {
        return view('articles.create', ['article' => new NewspaperArticle()]);
    }

    /**
     * ADMIN: persist a new article.
     */
    public function store(Request $request)
    {
        $data = $this->validated($request);

        $data['slug'] = NewspaperArticle::uniqueSlug($data['slug'] ?: $data['title']);

        $article = NewspaperArticle::create($data);

        return redirect()
            ->route('articles.show', $article)
            ->with('status', 'Article created.');
    }

    /**
     * ADMIN: edit form.
     */
    public function edit(NewspaperArticle $newspaperArticle)
    {
        return view('articles.edit', ['article' => $newspaperArticle]);
    }

    /**
     * ADMIN: update an article.
     */
    public function update(Request $request, NewspaperArticle $newspaperArticle)
    {
        $data = $this->validated($request);

        $data['slug'] = NewspaperArticle::uniqueSlug(
            $data['slug'] ?: $data['title'],
            $newspaperArticle->id
        );

        $newspaperArticle->update($data);

        return redirect()
            ->route('articles.show', $newspaperArticle)
            ->with('status', 'Article updated.');
    }

    /**
     * ADMIN: delete an article.
     */
    public function destroy(NewspaperArticle $newspaperArticle)
    {
        $newspaperArticle->delete();

        return redirect()
            ->route('articles.index')
            ->with('status', 'Article deleted.');
    }

    /**
     * Shared validation. The definitions textarea has one token per line in
     * the format:  word | pinyin | definition
     * Pinyin and definition are optional — punctuation and English words can
     * be just the bare word. Each line is parsed into the token format the
     * reading component consumes:
     *   [{word, entries:[{pinyin, pinyin_numeric, english}]}, ...]
     * Lines with no pinyin/definition get an empty entries array. No matching
     * against the body, and CEDICT is never consulted.
     */
    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'slug'             => ['nullable', 'string', 'max:255'],
            'summary'          => ['nullable', 'string'],
            'body'             => ['required', 'string'],
            'english'          => ['nullable', 'string'],
            'definitions_text' => ['nullable', 'string'],
            'hsk_level'        => ['nullable', 'integer', 'between:1,9'],
            'publication_date' => ['nullable', 'date'],
            'is_published'     => ['nullable', 'boolean'],
        ]);

        return [
            'title'            => $data['title'],
            'slug'             => $data['slug'] ?? '',
            'summary'          => $data['summary'] ?? null,
            'body'             => $data['body'],
            'english'          => $data['english'] ?? null,
            'hsk_level'        => $data['hsk_level'] ?? null,
            'publication_date' => $data['publication_date'] ?? null,
            'is_published'     => $request->boolean('is_published'),
            'definitions'      => $this->parseDefinitions($data['definitions_text'] ?? null),
        ];
    }

    /**
     * Parse the pipe-delimited definitions textarea into the token array.
     * One token per line:  word | pinyin | definition
     * Whitespace around each field is trimmed. A line with only a word (no
     * pinyin/definition) becomes a token with an empty entries array.
     */
    protected function parseDefinitions(?string $text): ?array
    {
        if ($text === null || trim($text) === '') {
            return null;
        }

        $tokens = [];
        foreach (preg_split('/\r\n|\r|\n/', $text) as $line) {
            if (trim($line) === '') {
                continue; // skip blank lines
            }

            // Split into at most 3 fields so a definition may itself contain "|".
            $parts   = explode('|', $line, 3);
            $word    = trim($parts[0]);
            if ($word === '') {
                continue;
            }
            $pinyin  = isset($parts[1]) ? trim($parts[1]) : '';
            $english = isset($parts[2]) ? trim($parts[2]) : '';

            $entries = ($pinyin === '' && $english === '')
                ? []
                : [[
                    'pinyin'         => $pinyin,
                    'pinyin_numeric' => '',
                    'english'        => $english,
                ]];

            $tokens[] = ['word' => $word, 'entries' => $entries];
        }

        return $tokens ?: null;
    }
}
