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
          AND u.created_at < DATE_SUB(NOW(), INTERVAL ? WEEK)
        GROUP BY u.id, u.created_at
    ', ['2026-06-05', 0]);

    $users = collect($rows)->map(fn ($r) => (object) [
        'age'      => (int) $r->account_age,
        'lifespan' => (int) $r->lifespan_days,
    ]);

    $labels = [];
    $percents = [];
    $eligibleCounts = [];

    for ($n = 0; $n <= 28; $n++) {
        // Only count a user toward day n once 4 weeks have passed since their
        // day n, so their lifespan has settled: start + n + 28 <= today.
        $eligible = $users->filter(fn ($u) => $u->age >= $n + 14);
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
        SELECT u.id, u.name, u.email, u.country,
               COUNT(g.id)                        AS gens,
               COUNT(DISTINCT DATE(g.created_at)) AS active_days,
               MAX(g.created_at)                  AS last_gen,
               u.created_at                       AS signed_up
        FROM users u
        INNER JOIN generated_texts g ON g.user_id = u.id
        GROUP BY u.id, u.name, u.email, u.country, u.created_at
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

Route::get('/signups', function () {
    abort_unless(auth()->id() === 1, 403);

    $rows = DB::select('
        SELECT u.email, u.country,
               COUNT(pv.id)       AS visits,
               MAX(pv.created_at) AS last_visited
        FROM premium_visits pv
        INNER JOIN users u ON u.id = pv.user_id
        GROUP BY u.id, u.email, u.country
        ORDER BY visits DESC
    ');

    return response()->json($rows, 200, [], JSON_PRETTY_PRINT);
})->middleware('auth')->name('signups');
/*
|--------------------------------------------------------------------------
| Raw export for post-processing
|--------------------------------------------------------------------------
| /gens — per-user generation counts on active days only, cap era only
| (the 4/day free limit went live 2026-06-27). Sample, stratify, and
| slice downstream (e.g. pandas), not here.
*/

Route::get('/gens', function () {
    abort_unless(auth()->id() === 1, 403);

    // Per-user counts for active days only, in chronological order.
    // No zero-fill, no shared calendar axis — each user's list just runs
    // from their first active day to their last. Only days after the free
    // daily cap was imposed (2026-06-27), so every count is cap-constrained.
    $rows = DB::select("
        SELECT user_id,
               COUNT(*) AS gens
        FROM generated_texts
        WHERE user_id IS NOT NULL
          AND DATE(created_at) > '2026-06-27'
        GROUP BY user_id, DATE(created_at)
        ORDER BY user_id, DATE(created_at)
    ");

    $users = [];
    foreach ($rows as $r) {
        $users[$r->user_id][] = (int) $r->gens;
    }

    return response()->json((object) $users, 200, [], JSON_PRETTY_PRINT);
})->middleware('auth')->name('raw.daily');
