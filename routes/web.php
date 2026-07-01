<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NewspaperArticleController;
use App\Http\Controllers\SavedWordController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\GeneratedText;
use App\Models\PremiumVisit;
use Illuminate\Http\Request;
use Laravel\Cashier\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::post('/stripe/webhook', [WebhookController::class, 'handleWebhook']);

Route::get('/billing', function (Request $request) {
    return $request->user()->redirectToBillingPortal(route('dashboard'));
})->middleware('auth')->name('billing');

Route::get('/premium', function () {
    PremiumVisit::create(['user_id' => auth()->id()]);
    return view('premium');
})->middleware('auth')->name('premium');

Route::post('/subscribe', function (Request $request) {
    return $request->user()
        ->newSubscription('default', config('services.stripe.price_id'))
        ->checkout([
            'success_url' => route('generate') . '?upgraded=1',
            'cancel_url'  => route('premium'),
        ]);
})->middleware('auth')->name('subscribe');

Route::get('/', function () {
    return view('welcome', ['story' => session('story'), 'chinese' => session('chinese'), 'english' => session('english'), 'definitions' => session('definitions'), 'apiDuration' => session('apiDuration')]);
});

Route::post('/', function () {
    preg_match_all('/\p{Han}/u', request('characters', ''), $matches);

    // Dedupe and re-index so it stays a clean JSON array
    $characters = array_values(array_unique($matches[0]));
    $characters = array_slice($characters,0,1100);

    // Creates the story using the Anthropic API
    $response = getStoryFromAnthropic($userId = null, $characters);
    $story = $response['story'];
    $apiDuration = $response['api_duration'] ?? null;

    //post-processing of the story from Anthropic API
    //split story into english and chinese by <hr>
    [$chinese, $english] = array_pad(explode('<hr>', $story ?? ''), 2, '');
    $chinese = trim($chinese);

    // Remove markdown bold (** **) from the English text
    $english = str_replace('**', '', $english);

    $definitions = getDefinitions($chinese);

    return redirect('/')
        ->with('story',       $story)
        ->with('chinese',     trim($chinese))
        ->with('english',     trim($english))
        ->with('definitions', $definitions)
        ->with('apiDuration', $apiDuration);
})->middleware('throttle:100,1');

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

// Privacy policy
Route::view('/privacy', 'privacy')->name('privacy');

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
        return redirect('generate');
    }

    $variety = $user->isPremium() ? request('variety') : null;


    $response = getStoryFromAnthropic($user->id, $characters, $variety);
    

    $charList = implode(' ', $characters);

    $story = $response['story'];
    $generated = $response['generated'];
    $apiDuration = $response['api_duration'] ?? null;

    //split story into english and chinese by <hr>
    [$chinese, $english] = array_pad(explode('<hr>', $story ?? ''), 2, '');

    $chinese = trim($chinese);

    // Remove markdown bold (** **) from the English text
    $english = str_replace('**', '', $english);

    $definitions = getDefinitions($chinese);

    // ── Remaining generations for the day ───────────────────
    $cap  = freeDailyGenerationCap();
    $remaining = $user->isPremium() ? null : max(0, $cap - ($used + 1));


    return view('story', [
        'story' => ($story),
        'chinese' => trim($chinese),
        'english' => trim($english),
        'definitions' => $definitions,
        'textId' => $generated->id,
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


Route::get('/texts/{text}', function (GeneratedText $text) {
    abort_unless($text->user_id === auth()->id(), 403);
    [$chinese, $english] = array_pad(explode('<hr>', $text->generated_text ?? ''), 2, '');

    $chinese = trim($chinese);

    // Remove markdown bold (** **) from the English text
    $english = str_replace('**', '', $english);

    $definitions = getDefinitions($chinese);
    
    return view('story', [
        'story' => $text->generated_text,
        'chinese' => trim(strip_tags($chinese)),
        'english' => trim($english),
        'definitions' => getDefinitions($chinese) ?? /* re-annotate fallback */ [],
        'textId' => $text->id,
        'savedWords' => auth()->user()->savedWords()->where('generated_text_id', $text->id)->get(['id', 'word', 'pinyin', 'english']), 
    ]);
})->middleware('auth');

Route::post('/saved-words', [SavedWordController::class, 'store']);

Route::delete('/saved-words/{savedWord}', [SavedWordController::class, 'destroy']);

/*
|--------------------------------------------------------------------------
| Newspaper Articles
|--------------------------------------------------------------------------
| Public reading (index + show) is open to guests and free users.
| Authoring (create/store/edit/update/destroy) is admin-only — the gate
| lives in NewspaperArticleController's constructor (auth()->id() === 1).
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


require __DIR__.'/auth.php';




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

Route::get('/saved-words', [SavedWordController::class, 'index'])->name('saved-words');
