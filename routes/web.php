<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes (entry point)
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider and assigned to the
| "web" middleware group. The public landing page and guest generation
| live here; everything else is grouped into dedicated files and pulled in
| via require at the bottom, keeping this file a simple map of the app.
|
|   auth.php          — login / register / password reset (Breeze)
|   subscription.php  — Stripe webhook, billing, premium, checkout
|   app.php           — dashboard, profile, story, characters, saved words,
|                       newspaper articles
|
*/

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

require __DIR__.'/auth.php';
require __DIR__.'/subscription.php';
require __DIR__.'/app.php';
