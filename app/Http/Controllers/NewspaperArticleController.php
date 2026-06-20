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
     * Shared validation. The glossary editor submits parallel arrays
     * (gloss_word[], gloss_pinyin[], gloss_english[]); each row is mapped 1:1
     * into the definitions token format the reading component consumes:
     *   [{word, entries:[{pinyin, pinyin_numeric, english}]}, ...]
     * No matching against the body, and CEDICT is never consulted.
     */
    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'slug'             => ['nullable', 'string', 'max:255'],
            'summary'          => ['nullable', 'string'],
            'body'             => ['required', 'string'],
            'english'          => ['nullable', 'string'],
            'hsk_level'        => ['nullable', 'integer', 'between:1,9'],
            'publication_date' => ['nullable', 'date'],
            'is_published'     => ['nullable', 'boolean'],
            'gloss_word'       => ['nullable', 'array'],
            'gloss_word.*'     => ['nullable', 'string', 'max:64'],
            'gloss_pinyin'     => ['nullable', 'array'],
            'gloss_pinyin.*'   => ['nullable', 'string', 'max:128'],
            'gloss_english'    => ['nullable', 'array'],
            'gloss_english.*'  => ['nullable', 'string', 'max:512'],
        ]);

        $words   = $request->input('gloss_word', []);
        $pinyins = $request->input('gloss_pinyin', []);
        $engs    = $request->input('gloss_english', []);

        // Each non-blank row -> one token in the component's "create format".
        $definitions = [];
        foreach ($words as $i => $word) {
            $word = trim((string) $word);
            if ($word === '') {
                continue; // skip empty rows
            }
            $definitions[] = [
                'word'    => $word,
                'entries' => [[
                    'pinyin'         => trim((string) ($pinyins[$i] ?? '')),
                    'pinyin_numeric' => '',
                    'english'        => trim((string) ($engs[$i] ?? '')),
                ]],
            ];
        }

        return [
            'title'            => $data['title'],
            'slug'             => $data['slug'] ?? '',
            'summary'          => $data['summary'] ?? null,
            'body'             => $data['body'],
            'english'          => $data['english'] ?? null,
            'hsk_level'        => $data['hsk_level'] ?? null,
            'publication_date' => $data['publication_date'] ?? null,
            'is_published'     => $request->boolean('is_published'),
            'definitions'      => $definitions ?: null,
        ];
    }
}
