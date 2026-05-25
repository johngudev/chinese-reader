<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

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

Route::get('/', function () {
    return view('welcome', ['story' => session('story')]);
});

Route::post('/', function () {
    preg_match_all('/\p{Han}/u', request('characters', ''), $matches);

    // Dedupe and re-index so it stays a clean JSON array
    $characters = array_values(array_unique($matches[0]));

    $charList = implode(' ', $characters);

    $response = Http::withHeaders([
        'x-api-key'         => env('X_API_KEY'),
        'anthropic-version' => '2023-06-01',
        'content-type'      => 'application/json',
    ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
        'model'      => 'claude-haiku-4-5-20251001',
        'max_tokens' => 2000,
        'system'     => 'You help people practice reading Chinese. Write a short, simple, coherent text in Simplified Chinese using the characters the user provides. It is OK to use a Chinese character or two outside that set but keep it minimal. The story may be a story, brief dialogue, a nonfiction piece. Standard punctuation is fine.  The Chinese text should be between 80-120 characters. With each story accompany it by a pinyin transliteration in the next paragraph.  Then follow it with a translation in English in the final paragraph.',
        'messages'   => [
            ['role' => 'user', 'content' => "Characters I know: {$charList}\n\nWrite me a text using only these characters."],
        ],
    ]);

    return redirect('/')->with('story', $response->json('content.0.text'));
})->middleware('throttle:5,1');

Route::get('/dashboard', function () {
    return redirect('/characters');
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/generate', function () {
    $user       = auth()->user();
    $characters = $user->charactersList?->characters_list ?? [];

    if (empty($characters)) {
        return response('Your character library is empty — add some first.');
    }

    $charList = implode(' ', $characters);

    $response = Http::withHeaders([
        'x-api-key'         => env('X_API_KEY'),
        'anthropic-version' => '2023-06-01',
        'content-type'      => 'application/json',
    ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
        'model'      => 'claude-haiku-4-5-20251001',
        'max_tokens' => 2000,
        'system'     => 'You help people practice reading Chinese. Write a short, simple, coherent text in Simplified Chinese using the characters the user provides. It is OK to use a Chinese character or two outside that set but keep it minimal. The story may be a story, brief dialogue, a nonfiction piece. Standard punctuation is fine.  The Chinese text should be between 80-120 characters. With each story accompany it by a pinyin transliteration in the next paragraph.  Then follow it with a translation in English in the final paragraph.',
        'messages'   => [
            ['role' => 'user', 'content' => "Characters I know: {$charList}\n\nWrite me a text using only these characters."],
        ],
    ]);

    return view('story', ['story' => ($response->json('content.0.text')) ]);
})->middleware('auth', 'throttle:5,1440')->name('generate');  // 50 generations per user per 24h;

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

require __DIR__.'/auth.php';
