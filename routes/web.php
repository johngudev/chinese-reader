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
    return view('welcome');
});

Route::get('/dashboard', function () {
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
        'max_tokens' => 500,
        'system'     => 'You help people practice reading Chinese. Write a short, simple, coherent story in Simplified Chinese using the characters the user provides. It is OK to use a Chinese character or two outside that set but keep it minimal. Standard punctuation is fine.  With each story accompany it by a pinyin transliteration in the next paragraph.  Then follow it with a translation in English in the final paragraph.',
        'messages'   => [
            ['role' => 'user', 'content' => "Characters I know: {$charList}\n\nWrite me a short story using only these characters."],
        ],
    ]);

    return view('story', ['story' => ($response->json('content.0.text')) ]);
})->middleware('auth');


require __DIR__.'/auth.php';
