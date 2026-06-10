<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\GeneratedText;
use App\Models\SavedWord;
use Illuminate\Http\Request;


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

Route::get('users', function () {
    abort_unless(auth()->id() === 1, 403);

    return User::all();
})->middleware(['auth', 'verified']);

Route::get('/', function () {

    

    return view('welcome', ['story' => session('story'), 'chinese' => session('chinese'), 'english' => session('english'), 'definitions' => session('definitions')]);
});

Route::post('/', function () {
    preg_match_all('/\p{Han}/u', request('characters', ''), $matches);

    // Dedupe and re-index so it stays a clean JSON array
    $characters = array_values(array_unique($matches[0]));
    $characters = array_slice($characters,0,1100);

    $char_diversity_note = "";

    if (count($characters) > 500) {
        //Character diversity for over 500 characters
        if (rand(1, 100) <= 40) {
            $char_diversity_note = " When creating your response, focus on using characters that are more rare in the Chinese language, as this will help me learn more. Also make each text you generate diverse, covering a wide array of contexts, subject matters, styles (fiction, nonfiction, narrative, essay), and so on.";
        }
    }

    if (rand(1, 100) <= 50) {
        $char_diversity_note = $char_diversity_note . " Do not talk about animals or fruit in your text.";
    }

    // Diversity for HSK4
    if ((count($characters) > 1000)) {
        $subvocab_ratio = 0.7;

        $char_cutoff_index = round($subvocab_ratio * 1000);

        $freq_chars = (config('vocab.characters'));

        $simpler_chars = array_slice($freq_chars,0,$char_cutoff_index);

        $difficult_chars = array_values(array_diff($characters, $simpler_chars));

        $keys_diff_char_sample = array_rand($difficult_chars, 100);

        $diff_char_sample = array_map(
            fn($k) => $difficult_chars[$k],
            $keys_diff_char_sample
        );

        $diff_char_sample_string = implode(' ', $diff_char_sample);

        $char_diversity_note = $char_diversity_note . " Focus on using the more difficult characters I know, such as ".$diff_char_sample_string;

        $text_type_number =rand(1,100);

        if ($text_type_number <= 40) {
            $char_diversity_note = $char_diversity_note . " The text should resemble a news story (you may include well-known proper nouns, like America, England, China, Japan, etc., instead of a narrative.";
        } else if($text_type_number < 70)  {
            $char_diversity_note = $char_diversity_note . " The text should resemble an information article, such as Encyclopedia entry, rather than a narrative.";
        }
    }

    $charList = implode(' ', $characters);



    //throttle request to max first 1,0000 characters

    $response = Http::withHeaders([
        'x-api-key'         => env('X_API_KEY'),
        'anthropic-version' => '2023-06-01',
        'content-type'      => 'application/json',
    ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
        'model'      => 'claude-haiku-4-5-20251001',
        'max_tokens' => 2000,
        'system'     => 'You help people practice reading Chinese. Write a short, simple, coherent text in Simplified Chinese using the characters the user provides. It is OK to use a Chinese character or two outside that set but keep it minimal. The story may be a story, brief dialogue, a nonfiction piece. Standard punctuation is fine.  The Chinese text should be between 80-120 characters. Each story should be purely in Chinese characters.  After the chinese text include an <hr> and follow with an English translation. If you give your text a title, please just have the title be the first sentence of the text (no extra linebreaks, or p tags to set off title)' . $char_diversity_note,
        'messages'   => [
            ['role' => 'user', 'content' => "Characters I know: {$charList}\n\nWrite me a text using only these characters." . $char_diversity_note],
        ],
    ]);

    $story = $response->json('content.0.text');

    //split story into english and chinese by <hr>
    [$chinese, $english] = array_pad(explode('<hr>', $story ?? ''), 2, '');

        // ---- Build $definitions: segment the Chinese and look up each word ----
        $chineseText = $chinese;                  // Chinese only — drop the English translation
        preg_match_all('/\p{Han}/u', $chineseText, $matches);             // keep only Han characters
        $chars  = $matches[0];
        $n      = count($chars);
        $maxLen = 8;
    
        // 1) every contiguous substring (length 1..maxLen) that appears in the text
        $candidates = [];
        for ($i = 0; $i < $n; $i++) {
            for ($len = 1; $len <= min($maxLen, $n - $i); $len++) {
                $candidates[implode('', array_slice($chars, $i, $len))] = true;
            }
        }
    
        // 2) one query; group by simplified because a word can have several entries (homographs, e.g. 行)
        $dict = DB::table('cedict')
            ->whereIn('simplified', array_keys($candidates))
            ->get(['simplified', 'pinyin', 'pinyin_numeric', 'english'])
            ->groupBy('simplified');
    
        // 3) greedy longest-match — no further queries
        $definitions = [];
        $i = 0;
        while ($i < $n) {
            $matched = null;
            for ($len = min($maxLen, $n - $i); $len >= 1; $len--) {
                $word = implode('', array_slice($chars, $i, $len));
                if (isset($dict[$word])) { $matched = [$word, $len]; break; }
            }
    
            if ($matched) {
                [$word, $len] = $matched;
                $definitions[] = [
                    'word'    => $word,
                    'entries' => $dict[$word]->map(fn ($e) => [
                        'pinyin'         => $e->pinyin,
                        'pinyin_numeric' => $e->pinyin_numeric,
                        'english'        => $e->english,
                    ])->all(),
                ];
                $i += $len;
            } else {
                // a Han character not in the dictionary (rare)
                $definitions[] = ['word' => $chars[$i], 'entries' => []];
                $i++;
            }
        }
        // ------------------------------------------------------------------------
    

    GeneratedText::create([
        'user_id' => null,
        'prompt' => "Characters I know: {$charList}\n\nWrite me a text using only these characters. {$char_diversity_note}",
        'generated_text' => $response->json('content.0.text'),
    ]);

    return redirect('/')
        ->with('story',       $story)
        ->with('chinese',     trim($chinese))
        ->with('english',     trim($english))
        ->with('definitions', $definitions);
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

/* Remove old generate code
Route::get('/generate', function () {
    $user       = auth()->user();
    $characters = $user->charactersList?->characters_list ?? [];

    if (empty($characters)) {
        return response('Your character library is empty — add some first.');
    }

    //throttle request to max first 1,200 characters
    // $characters = array_slice($characters, 1000);
    $characters = array_slice($characters,0,1200);

    $char_diversity_note = "";

    if (count($characters) > 500) {
        //Character diversity for over 500 characters
        if (rand(1, 100) <= 40) {
            $char_diversity_note = " When creating your response, focus on using characters that are more rare in the Chinese language, as this will help me learn more. Also make each text you generate diverse, covering a wide array of contexts, subject matters, styles (fiction, nonfiction, narrative, essay), and so on.";
        }
    }


    if (rand(1, 100) <= 50) {
        $char_diversity_note = $char_diversity_note . " Do not talk about animals or fruit in your text.";
    }

    // Diversity for HSK4
    if ((count($characters) > 1000)) {
        $subvocab_ratio = 0.7;

        $char_cutoff_index = round($subvocab_ratio * 1000);

        $freq_chars = (config('vocab.characters'));

        $simpler_chars = array_slice($freq_chars,0,$char_cutoff_index);

        $difficult_chars = array_values(array_diff($characters, $simpler_chars));

        $keys_diff_char_sample = array_rand($difficult_chars, 100);

        $diff_char_sample = array_map(
            fn($k) => $difficult_chars[$k],
            $keys_diff_char_sample
        );

        $diff_char_sample_string = implode(' ', $diff_char_sample);

        $char_diversity_note = $char_diversity_note . " Focus on using the more difficult characters I know, such as ".$diff_char_sample_string;

        $text_type_number =rand(1,100);

        if ($text_type_number <= 40) {
            $char_diversity_note = $char_diversity_note . " The text should resemble a news story (you may include well-known proper nouns, like America, England, China, Japan, etc., instead of a narrative.";
        } else if($text_type_number < 70)  {
            $char_diversity_note = $char_diversity_note . " The text should resemble an information article, such as Encyclopedia entry, rather than a narrative.";
        }
    }
    

    $charList = implode(' ', $characters);

    

    $response = Http::withHeaders([
        'x-api-key'         => env('X_API_KEY'),
        'anthropic-version' => '2023-06-01',
        'content-type'      => 'application/json',
    ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
        'model'      => 'claude-haiku-4-5-20251001',
        'max_tokens' => 2000,
        'system'     => 'You help people practice reading Chinese. Write a short, simple, coherent text in Simplified Chinese using the characters the user provides. It is OK to use a Chinese character or two outside that set but keep it minimal. The story may be a story, brief dialogue, a nonfiction piece. Standard punctuation is fine.  The Chinese text should be between 80-120 characters. Each story should be purely in Chinese characters.  After the chinese text include an <hr> and follow with an English translation.' . $char_diversity_note,
        'messages'   => [
            ['role' => 'user', 'content' => "Characters I know: {$charList}\n\nWrite me a text using only these characters. {$char_diversity_note}"],
        ],
    ]);

    GeneratedText::create([
        'user_id' => $user->id,
        'prompt' => "Characters I know: {$charList}\n\nWrite me a text using only these characters. {$char_diversity_note}",
        'generated_text' => $response->json('content.0.text'),
    ]);

    return view('story', ['story' => ($response->json('content.0.text')) ]);
})->middleware('auth', 'throttle:50,1440');  // 50 generations per user per 24h;
*/

// Show the form (pre-filled with their current list)
Route::get('/characters', function () {
    $characters = auth()->user()->charactersList?->characters_list ?? [];
    return view('characters', ['characters' => $characters])

    ;
})->middleware('auth')->name('characters');;

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

Route::get('/retention', function () {
    abort_unless(auth()->id() === 1, 403);

    $rows = DB::select('
        SELECT u.id,
               DATEDIFF(NOW(), u.created_at)             AS account_age,
               DATEDIFF(MAX(g.created_at), u.created_at) AS lifespan_days
        FROM users u
        INNER JOIN generated_texts g ON g.user_id = u.id
        GROUP BY u.id, u.created_at
    ');

    $users = collect($rows)->map(fn ($r) => (object) [
        'age'      => (int) $r->account_age,
        'lifespan' => (int) $r->lifespan_days,
    ]);

    $labels = [];
    $percents = [];
    $eligibleCounts = [];

    for ($n = 0; $n <= ((int) $users->max('age') - 11); $n++) {
        $eligible = $users->filter(fn ($u) => $u->age >= $n);
        if ($eligible->isEmpty()) break;

        $retained = $eligible->filter(fn ($u) => $u->lifespan >= $n)->count();

        $labels[]         = $n;
        $percents[]       = round($retained / $eligible->count() * 100, 1);
        $eligibleCounts[] = $eligible->count();
    }

    $registered = User::count();
    $activated  = $users->count();

    return view('retention-curve', [
        'labels'           => $labels,
        'percents'         => $percents,
        'eligibleCounts'   => $eligibleCounts,
        'totalUsers'       => $registered,
        'activationPct'    => $registered ? round($activated / $registered * 100, 1) : 0,
        'totalGenerations' => GeneratedText::count(),
    ]);
})->middleware('auth')->name('retention');


Route::get('/generate', function () {
    $user       = auth()->user();
    $characters = $user->charactersList?->characters_list ?? [];

    if (empty($characters)) {
        return response('Your character library is empty — add some first.');
    }

    //throttle request to max first 1,200 characters
    // $characters = array_slice($characters, 1000);
    $characters = array_slice($characters,0,1200);

    $char_diversity_note = "";

    if (count($characters) > 500) {
        //Character diversity for over 500 characters
        if (rand(1, 100) <= 40) {
            $char_diversity_note = " When creating your response, focus on using characters that are more rare in the Chinese language, as this will help me learn more. Also make each text you generate diverse, covering a wide array of contexts, subject matters, styles (fiction, nonfiction, narrative, essay), and so on.";
        }
    }


    if (rand(1, 100) <= 50) {
        $char_diversity_note = $char_diversity_note . " Do not talk about animals or fruit in your text.";
    }

    // Diversity for HSK4
    if ((count($characters) > 1000)) {
        $subvocab_ratio = 0.7;

        $char_cutoff_index = round($subvocab_ratio * 1000);

        $freq_chars = (config('vocab.characters'));

        $simpler_chars = array_slice($freq_chars,0,$char_cutoff_index);

        $difficult_chars = array_values(array_diff($characters, $simpler_chars));

        $keys_diff_char_sample = array_rand($difficult_chars, 100);

        $diff_char_sample = array_map(
            fn($k) => $difficult_chars[$k],
            $keys_diff_char_sample
        );

        $diff_char_sample_string = implode(' ', $diff_char_sample);

        $char_diversity_note = $char_diversity_note . " Focus on using the more difficult characters I know, such as ".$diff_char_sample_string;

        $text_type_number =rand(1,100);

        if ($text_type_number <= 40) {
            $char_diversity_note = $char_diversity_note . " The text should resemble a news story (you may include well-known proper nouns, like America, England, China, Japan, etc., instead of a narrative.";
        } else if($text_type_number < 70)  {
            $char_diversity_note = $char_diversity_note . " The text should resemble an information article, such as Encyclopedia entry, rather than a narrative.";
        }
    }
    

    $charList = implode(' ', $characters);

    

    $response = Http::withHeaders([
        'x-api-key'         => env('X_API_KEY'),
        'anthropic-version' => '2023-06-01',
        'content-type'      => 'application/json',
    ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
        'model'      => 'claude-haiku-4-5-20251001',
        'max_tokens' => 2000,
        'system'     => 'You help people practice reading Chinese. Write a short, simple, coherent text in Simplified Chinese using the characters the user provides. It is OK to use a Chinese character or two outside that set but keep it minimal. The story may be a story, brief dialogue, a nonfiction piece. Standard punctuation is fine.  The Chinese text should be between 80-120 characters. Each story should be purely in Chinese characters.  After the chinese text include an <hr> and follow with an English translation. If you give your text a title, please just have the title be the first sentence of the text (no extra linebreaks, or p tags to set off title)' . $char_diversity_note,
        'messages'   => [
            ['role' => 'user', 'content' => "Characters I know: {$charList}\n\nWrite me a text using only these characters. {$char_diversity_note}"],
        ],
    ]);

    $story = $response->json('content.0.text');

    //split story into english and chinese by <hr>
    [$chinese, $english] = array_pad(explode('<hr>', $story ?? ''), 2, '');


    // ---- Build $definitions: segment the Chinese and look up each word ----
    $chineseText = $chinese;                  // Chinese only — drop the English translation
    preg_match_all('/\p{Han}/u', $chineseText, $matches);             // keep only Han characters
    $chars  = $matches[0];
    $n      = count($chars);
    $maxLen = 8;

    // 1) every contiguous substring (length 1..maxLen) that appears in the text
    $candidates = [];
    for ($i = 0; $i < $n; $i++) {
        for ($len = 1; $len <= min($maxLen, $n - $i); $len++) {
            $candidates[implode('', array_slice($chars, $i, $len))] = true;
        }
    }

    // 2) one query; group by simplified because a word can have several entries (homographs, e.g. 行)
    $dict = DB::table('cedict')
        ->whereIn('simplified', array_keys($candidates))
        ->get(['simplified', 'pinyin', 'pinyin_numeric', 'english'])
        ->groupBy('simplified');

    // 3) greedy longest-match — no further queries
    $definitions = [];
    $i = 0;
    while ($i < $n) {
        $matched = null;
        for ($len = min($maxLen, $n - $i); $len >= 1; $len--) {
            $word = implode('', array_slice($chars, $i, $len));
            if (isset($dict[$word])) { $matched = [$word, $len]; break; }
        }

        if ($matched) {
            [$word, $len] = $matched;
            $definitions[] = [
                'word'    => $word,
                'entries' => $dict[$word]->map(fn ($e) => [
                    'pinyin'         => $e->pinyin,
                    'pinyin_numeric' => $e->pinyin_numeric,
                    'english'        => $e->english,
                ])->all(),
            ];
            $i += $len;
        } else {
            // a Han character not in the dictionary (rare)
            $definitions[] = ['word' => $chars[$i], 'entries' => []];
            $i++;
        }
    }
    // ------------------------------------------------------------------------
    

    $generated = GeneratedText::create([
        'user_id' => $user->id,
        'prompt' => "Characters I know: {$charList}\n\nWrite me a text using only these characters. {$char_diversity_note}",
        'generated_text' => $response->json('content.0.text'),
    ]);


    return view('story', [
        'story' => ($response->json('content.0.text')),
        'chinese' => trim($chinese),
        'english' => trim($english),
        'definitions' => $definitions,
        'textId' => $generated->id,
        'savedWords' => []]);
})->middleware('auth', 'throttle:100,1440');  // 50 generations per user per 24h;


Route::get('/history', function () {
    $texts = auth()->user()->generatedTexts()->latest()->take(10)->get();
    return view('my-texts', ['texts' => $texts]);
})->middleware('auth')->name('history');


Route::get('/texts/{text}', function (GeneratedText $text) {
    abort_unless($text->user_id === auth()->id(), 403);
    [$chinese, $english] = array_pad(explode('<hr>', $text->generated_text ?? ''), 2, '');

    return view('story', [
        'story' => $text->generated_text,
        'chinese' => trim(strip_tags($chinese)),
        'english' => trim($english),
        'definitions' => getDefinitions($chinese) ?? /* re-annotate fallback */ [],
        'textId' => $text->id,
        'savedWords' => auth()->user()->savedWords()->where('generated_text_id', $text->id)->get(['id', 'word', 'pinyin', 'english']), 
    ]);
})->middleware('auth');

Route::post('/saved-words', function (Request $r) {
    $data = $r->validate([
        'generated_text_id' => 'nullable|integer|exists:generated_texts,id',
        'word'              => 'required|string|max:32',
        'pinyin'            => 'nullable|string|max:255',
        'english'           => 'nullable|string',
    ]);
    // updateOrCreate = idempotent; double-clicks return the same row
    $word = auth()->user()->savedWords()->updateOrCreate(
        ['generated_text_id' => $data['generated_text_id'] ?? null, 'word' => $data['word']],
        ['pinyin' => $data['pinyin'] ?? null, 'english' => $data['english'] ?? null],
    );
    return response()->json($word);   // includes id, needed for delete
})->middleware('auth');

Route::delete('/saved-words/{savedWord}', function (SavedWord $savedWord) {
    abort_unless($savedWord->user_id === auth()->id(), 403);   // ownership check
    $savedWord->delete();
    return response()->noContent();
})->middleware('auth');

require __DIR__.'/auth.php';

function getDefinitions(string $chinese, int $maxLen = 8): array
{
    preg_match_all('/\p{Han}/u', $chinese, $matches);
    $chars = $matches[0];
    $n     = count($chars);

    if ($n === 0) return [];

    // 1) every contiguous substring (length 1..maxLen)
    $candidates = [];
    for ($i = 0; $i < $n; $i++) {
        for ($len = 1; $len <= min($maxLen, $n - $i); $len++) {
            $candidates[implode('', array_slice($chars, $i, $len))] = true;
        }
    }

    // 2) one batched query; group by simplified (homographs → several entries)
    $dict = DB::table('cedict')
        ->whereIn('simplified', array_keys($candidates))
        ->get(['simplified', 'pinyin', 'pinyin_numeric', 'english'])
        ->groupBy('simplified');

    // 3) greedy longest-match — no further queries
    $definitions = [];
    $i = 0;
    while ($i < $n) {
        $matched = null;
        for ($len = min($maxLen, $n - $i); $len >= 1; $len--) {
            $word = implode('', array_slice($chars, $i, $len));
            if (isset($dict[$word])) { $matched = [$word, $len]; break; }
        }

        if ($matched) {
            [$word, $len] = $matched;
            $definitions[] = [
                'word'    => $word,
                'entries' => $dict[$word]->map(fn ($e) => [
                    'pinyin'         => $e->pinyin,
                    'pinyin_numeric' => $e->pinyin_numeric,
                    'english'        => $e->english,
                ])->all(),
            ];
            $i += $len;
        } else {
            $definitions[] = ['word' => $chars[$i], 'entries' => []];  // Han char not in dict (rare)
            $i++;
        }
    }

    return $definitions;
}
