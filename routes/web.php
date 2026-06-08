<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\GeneratedText;

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

    

    return view('welcome', ['story' => session('story')]);
});

Route::post('/', function () {
    preg_match_all('/\p{Han}/u', request('characters', ''), $matches);

    // Dedupe and re-index so it stays a clean JSON array
    $characters = array_values(array_unique($matches[0]));
    $characters = array_slice($characters,0,1100);

    $char_diversity_note = "";

    if (count($characters) > 500) {
        $char_diversity_note = " When creating your response, focus on using characters that are more rare in the Chinese language, as this will help me learn more. Also make each text you generate diverse, covering a wide array of contexts, subject matters, styles (fiction, nonfiction, narrative, essay), and so on.";
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
        'system'     => 'You help people practice reading Chinese. Write a short, simple, coherent text in Simplified Chinese using the characters the user provides. It is OK to use a Chinese character or two outside that set but keep it minimal. The story may be a story, brief dialogue, a nonfiction piece. Standard punctuation is fine.  The Chinese text should be between 80-120 characters. Each story should be purely in Chinese characters.  After the chinese text include an <hr> and follow with an English translation.' . $char_diversity_note,
        'messages'   => [
            ['role' => 'user', 'content' => "Characters I know: {$charList}\n\nWrite me a text using only these characters." . $char_diversity_note],
        ],
    ]);


    GeneratedText::create([
        'user_id' => null,
        'prompt' => "Characters I know: {$charList}\n\nWrite me a text using only these characters. {$char_diversity_note}",
        'generated_text' => $response->json('content.0.text'),
    ]);

    return redirect('/')->with('story', $response->json('content.0.text'));


})->middleware('throttle:50,1');

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
        $char_diversity_note = " When creating your response, focus on using characters that are more rare in the Chinese language, as this will help me learn more.";
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

Route::get('/generated-texts', function () {
    abort_unless(auth()->id() === 1, 403);


    $generatedTexts = GeneratedText::all()->count();


    return ($generatedTexts);
})->middleware('auth')->name('generated_texts');

require __DIR__.'/auth.php';
