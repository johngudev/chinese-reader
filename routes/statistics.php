<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\GeneratedText;

/*
|--------------------------------------------------------------------------
| Statistics Routes
|--------------------------------------------------------------------------
| Super-user only data-collection routes. Every route is gated to
| auth()->id() === 1. Loaded via the "web" middleware group in the
| RouteServiceProvider.
*/

Route::get('users', function () {
    abort_unless(auth()->id() === 1, 403);

    return User::all();
})->middleware(['auth', 'verified']);

Route::get('/retention', function () {
    abort_unless(auth()->id() === 1, 403);

    $rows = DB::select('
        SELECT u.id,
               DATEDIFF(NOW(), u.created_at)             AS account_age,
               DATEDIFF(MAX(g.created_at), u.created_at) AS lifespan_days
        FROM users u
        INNER JOIN generated_texts g ON g.user_id = u.id
        WHERE u.created_at > ?
        GROUP BY u.id, u.created_at
    ', ['2026-06-05']);

    $users = collect($rows)->map(fn ($r) => (object) [
        'age'      => (int) $r->account_age,
        'lifespan' => (int) $r->lifespan_days,
    ]);

    $labels = [];
    $percents = [];
    $eligibleCounts = [];

    for ($n = 0; $n <= ((int) $users->max('age')-7); $n++) {
        $eligible = $users->filter(fn ($u) => $u->age >= $n);
        if ($eligible->isEmpty()) break;

        $retained = $eligible->filter(fn ($u) => $u->lifespan >= $n)->count();

        $labels[]         = $n;
        $percents[]       = round($retained / $eligible->count() * 100, 1);
        $eligibleCounts[] = $eligible->count();
    }

    $registered = User::count();
    $activated  = GeneratedText::distinct()->count('user_id');

    return view('retention-curve', [
        'labels'           => $labels,
        'percents'         => $percents,
        'eligibleCounts'   => $eligibleCounts,
        'totalUsers'       => $registered,
        'activationPct'    => $registered ? round($activated / $registered * 100, 1) : 0,
        'totalGenerations' => GeneratedText::count(),
    ]);
})->middleware('auth')->name('retention');

Route::get('/analzye', function() {
    abort_unless(auth()->id() === 1, 403);

    $charactersList = User::find(426)->charactersList;

    return $charactersList;
})->middleware('auth')->name('analyze');

Route::get('/outreach', function () {
    abort_unless(auth()->id() === 1, 403);

    $rows = DB::select('
        SELECT u.id, u.name, u.email,
               COUNT(g.id)                        AS gens,
               COUNT(DISTINCT DATE(g.created_at)) AS active_days,
               MAX(g.created_at)                  AS last_gen,
               u.created_at                       AS signed_up
        FROM users u
        INNER JOIN generated_texts g ON g.user_id = u.id
        GROUP BY u.id, u.name, u.email, u.created_at
    ');

    $users = collect($rows);

    // Interview targets: came back on 3+ separate days — the sticky tail
    $power = $users->filter(fn ($u) => $u->gens >= 10)
        ->sortByDesc(fn ($u) => [$u->active_days, $u->gens])
        ->values();

    return response()->json([
        'power_users'   => ['count' => $power->count(),   'users' => $power],
    ], 200, [], JSON_PRETTY_PRINT);
})->middleware('auth');