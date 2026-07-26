<?php

use App\Http\Controllers\NewspaperArticleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SavedWordController;
use App\Models\GeneratedText;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
| The authenticated app surface: dashboard, profile, story generation and
| history, the character list, saved words, and newspaper articles. Pulled
| in via require from web.php, so these inherit the "web" middleware group.
*/

Route::get('/dashboard', function () {
    return redirect('/characters');
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/story', function () {
    return view('story');
})->middleware('auth')->name('generate');



// Save the pasted characters (overrides existing)
Route::post('/characters', function () {
    // Extract only Han characters — ignores spaces, punctuation, English, numbers
    preg_match_all('/\p{Han}/u', request('characters', ''), $matches);

    // Dedupe and re-index so it stays a clean JSON array
    $characters = array_values(array_unique($matches[0]));

    auth()->user()->charactersList()->updateOrCreate(
        [],                                  // empty = this user's existing row, if any
        ['characters_list' => $characters]
    );
    return redirect('/characters')
        ->with('status', count($characters) . ' characters saved.');
})->middleware('auth');


Route::post('/generate', function () {
    $user       = auth()->user();

            // ── Free daily generation cap ──────────────────────────
                $cap  = freeDailyGenerationCap();
                $used = generationsUsedToday($user);

                if (! $user->isPremium() && $used >= $cap) {
                    return view('story', [
                        'story'    => null,
                        'locked'   => true,
                        'resetsAt' => now()->addDay()->startOfDay()->toIso8601String(),
                    ]);
                }
            // -- End daily generation cap logic --------------------


    $characters = $user->charactersList?->characters_list ?? [];

    //if there was a request, instead use characters from characters sent in request
    if(request('characters')) {
        // Extract only Han characters — ignores spaces, punctuation, English, numbers
        preg_match_all('/\p{Han}/u', request('characters', ''), $matches);

        // Dedupe and re-index so it stays a clean JSON array
        $characters = array_values(array_unique($matches[0]));
    }

    if (empty($characters)) {
        // No characters list yet — send them to set one up. (Was
        // redirect('generate'), which pointed at the URL /generate — a
        // POST-only path — and threw MethodNotAllowedHttpException.)
        return redirect('/characters')
            ->with('status', 'Add the characters you know first — then we can write you a text.');
    }

    $variety = $user->isPremium() ? request('variety') : null;

    // Theme chip (premium, curated list in config/topics.php).
    $theme = $user->isPremium() ? request('theme') : null;

    // ── Focus words (premium, Advanced box) ────────────────
    $focusWords = [];
    if ($user->isPremium() && request('focus_words')) {
        $candidates = collect(preg_split('/[,，、\r\n]+/u', request('focus_words')))
            ->map(fn ($w) => preg_replace('/^\s+|\s+$/u', '', $w))  // trim edges only (incl. full-width space)
            ->filter()                                             // drop empty tokens, e.g. trailing comma
            ->unique()
            ->take(5)
            ->values();

        if ($candidates->isNotEmpty()) {
            // cedict is the sole arbiter of a real word; non-matches silently dropped
            $focusWords = DB::table('cedict')
                ->whereIn('simplified', $candidates->all())
                ->distinct()
                ->pluck('simplified')
                ->all();
        }
    }

    $response = getStoryFromAnthropic($user->id, $characters, $variety, $focusWords, $theme);


    $charList = implode(' ', $characters);

    $story = $response['story'];
    $generated = $response['generated'];
    $apiDuration = $response['api_duration'] ?? null;

    //split story into english and chinese by <hr>
    [$chinese, $english] = array_pad(explode('<hr>', $story ?? ''), 2, '');

    $chinese = trim($chinese);

    // Remove markdown bold (** **) from the English text
    $english = str_replace('**', '', $english);

    $definitions = getDefinitions($chinese, $characters);

    // These definitions carry new_character_flag data (built against the
    // user's characters list). Not consumed by the UI yet.
    $definitionsFlagged = !empty($characters);

    // ── Remaining generations for the day ───────────────────
    $cap  = freeDailyGenerationCap();
    $remaining = $user->isPremium() ? null : max(0, $cap - ($used + 1));


    return view('story', [
        'story' => ($story),
        'chinese' => trim($chinese),
        'english' => trim($english),
        'definitions' => $definitions,
        'textId' => $generated->id,
        'isOwner' => true,
        'savedWords' => [],
        'locked' => false,
        'remaining' => $remaining,
        'apiDuration' => $apiDuration,
        ]);


})->middleware('auth');

/* Turn off get route for generation
Route::get('/generate', function () {
    $user       = auth()->user();
    $characters = $user->charactersList?->characters_list ?? [];

    // Creates the story using the Anthropic API
    $response = getStoryFromAnthropic($user->id, $characters);

    $story = $response['story'];
    $generated = $response['generated'];

    //split story into english and chinese by <hr>
    [$chinese, $english] = array_pad(explode('<hr>', $story ?? ''), 2, '');

    $chinese = trim($chinese);

    // Remove markdown bold (** **) from the English text
    $english = str_replace('**', '', $english);

    $definitions = getDefinitions($chinese);


    return view('story', [
        'story' => $story,
        'chinese' => trim($chinese),
        'english' => trim($english),
        'definitions' => $definitions,
        'textId' => $generated->id,
        'savedWords' => []]);
})->middleware('auth', 'throttle:100,1440');  // 50 generations per user per 24h; 
*/

Route::get('/history', function () {
    $texts = auth()->user()->generatedTexts()->latest()->take(10)->get();
    return view('my-texts', ['texts' => $texts]);
})->middleware('auth')->name('history');


// Public: any text is readable by anyone (guests included). Only the
// owner gets the saved-words feature — everyone else is read-only.
Route::get('/texts/{text}', function (GeneratedText $text) {
    $isOwner = auth()->check() && auth()->id() === $text->user_id;

    [$chinese, $english] = array_pad(explode('<hr>', $text->generated_text ?? ''), 2, '');

    $chinese = trim($chinese);

    // Remove markdown bold (** **) from the English text
    $english = str_replace('**', '', $english);

    $definitions = getDefinitions($chinese);

    return view('story', [
        'story' => $text->generated_text,
        'chinese' => trim(strip_tags($chinese)),
        'english' => trim($english),
        'definitions' => $definitions ?? [],
        'textId' => $text->id,
        'isOwner' => $isOwner,
        'savedWords' => $isOwner
            ? auth()->user()->savedWords()->where('generated_text_id', $text->id)->get(['id', 'word', 'pinyin', 'english'])
            : [],
    ]);
})->middleware('throttle:60,1');

Route::post('/saved-words', [SavedWordController::class, 'store']);

// Union a saved word's characters into the user's characters list.
Route::post('/saved-words/{savedWord}/promote', [SavedWordController::class, 'promote']);

Route::delete('/saved-words/{savedWord}', [SavedWordController::class, 'destroy']);

Route::get('/saved-words', [SavedWordController::class, 'index'])->name('saved-words');

/*
|--------------------------------------------------------------------------
| Newspaper Articles
|--------------------------------------------------------------------------
| Public reading (index + show) is open to guests and free users.
| Authoring (create/store/edit/update/destroy) is admin-only — the gate
| lives in NewspaperArticleController's constructor (User::isAdmin()).
| Admin routes live under /admin/articles so they don't collide with the
| public /articles/{slug} wildcard.
*/

// Public — no auth
Route::get('/articles', [NewspaperArticleController::class, 'index'])->name('articles.index');

// Admin authoring (registered before the public wildcard)
Route::middleware('auth')->group(function () {
    Route::get('/admin/articles/create',            [NewspaperArticleController::class, 'create'])->name('articles.create');
    Route::post('/admin/articles',                  [NewspaperArticleController::class, 'store'])->name('articles.store');
    Route::get('/admin/articles/{newspaperArticle}/edit', [NewspaperArticleController::class, 'edit'])->name('articles.edit');
    Route::put('/admin/articles/{newspaperArticle}', [NewspaperArticleController::class, 'update'])->name('articles.update');
    Route::delete('/admin/articles/{newspaperArticle}', [NewspaperArticleController::class, 'destroy'])->name('articles.destroy');
});

// Public — read one article (wildcard last so it doesn't shadow the routes above)
Route::get('/articles/{newspaperArticle}', [NewspaperArticleController::class, 'show'])->name('articles.show');

    // Show the form (pre-filled with their current list)
Route::get('/characters', function () {

    $characters = auth()->user()->charactersList?->characters_list ?? [];

    $lists = collect(config('vocab.lists'));

    $vocabLists = [
        ['vocab_list_name' => '(Old) HSK Level 1', 'characters' => $lists->firstWhere('vocab_list_name', '(Old) HSK 1')['characters']],
        ['vocab_list_name' => '(Old) HSK Level 2', 'characters' => $lists->firstWhere('vocab_list_name', '(Old) HSK 2')['characters']],
        ['vocab_list_name' => '(Old) HSK Level 3', 'characters' => $lists->firstWhere('vocab_list_name', '(Old) HSK 3')['characters']],
        ['vocab_list_name' => '(Old) HSK Level 4', 'characters' => $lists->firstWhere('vocab_list_name', '(Old) HSK 4')['characters']],
    ];

    return view('characters', ['characters' => $characters, 'vocabLists' => $vocabLists]);
})->middleware('auth')->name('characters');;

//Dashboard
Route::get('/dash', function () {
    $user = auth()->user();

    // ── texts: totals + unique characters seen ──────────────
    $texts = $user->generatedTexts()->latest()->limit(500)->pluck('generated_text');
    $textsTotal = $user->generatedTexts()->count();

    preg_match_all('/\p{Han}/u', $texts->implode(''), $m);
    $charsSeen = count(array_unique($m[0]));

    // ── activity: per-day counts, last 30 days, zero-filled ─
    $raw = $user->generatedTexts()
        ->where('created_at', '>=', now()->subDays(6)->startOfDay())
        ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
        ->groupBy('d')->pluck('c', 'd');

    $activityLabels = [];
    $activityCounts = [];
    for ($i = 6; $i >= 0; $i--) {
        $day = now()->subDays($i)->toDateString();
        $activityLabels[] = now()->subDays($i)->format('M j');
        $activityCounts[] = (int) ($raw[$day] ?? 0);
    }

    // ── streaks: walk distinct generation dates ─────────────
    $dates = $user->generatedTexts()
        ->selectRaw('DISTINCT DATE(created_at) as d')
        ->orderBy('d')->pluck('d')->all();

    $streakLongest = 0; $run = 0; $prev = null;
    foreach ($dates as $d) {
        $run = ($prev && \Carbon\Carbon::parse($d)->diffInDays($prev) === 1) ? $run + 1 : 1;
        $streakLongest = max($streakLongest, $run);
        $prev = \Carbon\Carbon::parse($d);
    }
    $last = $dates ? \Carbon\Carbon::parse(end($dates)) : null;
    $streakCurrent = ($last && $last->diffInDays(today()) <= 1) ? $run : 0;

    // ── saved words ─────────────────────────────────────────
    $wordsTotal  = $user->savedWords()->count();
    $wordsRecent = $user->savedWords()->where('created_at', '>=', now()->subWeek())->count();
    $deck        = $user->savedWords()->latest()->limit(5)->get(['id', 'word', 'pinyin', 'english']);

    // ── progress toward next milestone ──────────────────────
    $milestones = [250, 500, 1000, 2000, 3000, 4000, 5000];
    $current = count($user->charactersList?->characters_list ?? []);

    $next = collect($milestones)->first(fn ($m) => $current < $m);
    $progress = [
        'current'   => $current,
        'milestone' => $next ?? 5000,
        'pct'       => $next ? (int) round(100 * $current / $next) : 100,
        'maxed'     => $next === null,
    ];

    $myCharacters = implode(' ', $user->charactersList?->characters_list ?? []);

    // ── library: recent texts ───────────────────────────────
    $recentTexts = $user->generatedTexts()->latest()->limit(6)
    ->get(['id', 'generated_text', 'created_at'])
    ->map(function ($t) {
        $chinese = trim(strip_tags(Str::before($t->generated_text, '<hr>')));
        $english = trim(strip_tags(Str::after($t->generated_text, '<hr>')));
        $english = preg_replace('/\*+/', '', $english);   // strip markdown ** from titles

        return [
            'id'      => $t->id,
            'snippet' => mb_substr($chinese, 0, 24) . (mb_strlen($chinese) > 24 ? '…' : ''),
            'date'    => $t->created_at->format('M j'),
            'chars'   => mb_strlen(preg_replace('/[^\p{Han}]/u', '', $chinese)),
        ];
    });

    return view('dashboard', compact(
        'streakCurrent', 'streakLongest', 'charsSeen',
        'wordsTotal', 'wordsRecent', 'textsTotal',
        'activityLabels', 'activityCounts', 'progress', 'deck', 'recentTexts', 'myCharacters'
    ) + ['memberSince' => $user->created_at->format('M Y')]);
})->middleware('auth')->name('dash');
