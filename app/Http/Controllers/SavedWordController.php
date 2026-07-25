<?php

namespace App\Http\Controllers;

use App\Models\SavedWord;
use App\Models\UserCharactersList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SavedWordController extends Controller
{
    /**
     * Every action requires an authenticated user.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * The reader's saved-word list. Free users see page 1 in full; from
     * page 2 on the list is locked to a 5-word teaser behind the blur.
     */
    public function index()
    {
        $user = auth()->user();

        $words = $user->savedWords()->latest()
            ->paginate(10, ['id', 'generated_text_id', 'word', 'pinyin', 'english']);

        // If the user is not premium, lock the list after page 1
        $locked = ! $user->isPremium() && $words->currentPage() >= 2;

        // Locked pages only show a 5-word teaser behind the blur
        if ($locked) {
            $words->setCollection($words->getCollection()->take(5));
        }

        // Promoted state is derived, not stored: a word counts as already
        // added when every Han character in it is in the characters list.
        // array_flip gives O(1) membership against a list up to 1100 long.
        $known = array_flip($user->charactersList?->characters_list ?? []);

        $payload = $words->getCollection()->map(function (SavedWord $w) use ($known) {
            $chars = hanCharacters($w->word);

            return [
                'id'        => $w->id,
                'word'      => $w->word,
                'pinyin'    => $w->pinyin,
                'english'   => $w->english,
                'source_id' => $w->generated_text_id,
                'chars'     => $chars,
                // array_values matters: array_filter preserves keys, and a
                // sparse array would reach the view as a JS object.
                'new_chars' => array_values(array_filter($chars, fn ($c) => ! isset($known[$c]))),
            ];
        })->values();

        return view('saved-words', [
            'words'   => $words,      // still used for isEmpty() and links()
            'locked'  => $locked,
            'payload' => $payload,    // drives the Alpine component
        ]);
    }

    /**
     * Save a word. updateOrCreate = idempotent; double-clicks return the
     * same row. The JSON response includes the id, needed for delete.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'generated_text_id' => 'nullable|integer|exists:generated_texts,id',
            'word'              => 'required|string|max:32',
            'pinyin'            => 'nullable|string|max:255',
            'english'           => 'nullable|string',
        ]);

        $word = auth()->user()->savedWords()->updateOrCreate(
            ['generated_text_id' => $data['generated_text_id'] ?? null, 'word' => $data['word']],
            ['pinyin' => $data['pinyin'] ?? null, 'english' => $data['english'] ?? null],
        );

        return response()->json($word);
    }

    /**
     * Union a saved word's characters into the user's characters list.
     *
     * The route is keyed on the saved-word id rather than a character
     * string: ownership is checkable, and the server derives the
     * characters itself instead of trusting the client.
     *
     * The client currently ignores this response — it greys the button and
     * flashes a toast without waiting. The payload is returned anyway for
     * the follow-up that adds failure handling.
     */
    public function promote(SavedWord $savedWord)
    {
        abort_unless($savedWord->user_id === auth()->id(), 403);   // ownership check

        $chars = hanCharacters($savedWord->word);

        if (empty($chars)) {
            return response()->json(['added' => [], 'total' => null]);
        }

        $max = (int) config('app.max_characters_list', 1100);

        // characters_list is a JSON column updated read-modify-write, and
        // this UI invites fast repeated clicks — without the lock, two
        // concurrent promotes silently clobber one another.
        $result = DB::transaction(function () use ($chars, $max) {
            // Create-then-lock: lockForUpdate can't lock a row that doesn't
            // exist, and a user who never visited /characters has none.
            $row = auth()->user()->charactersList()->firstOrCreate([], ['characters_list' => []]);

            $list = UserCharactersList::whereKey($row->id)->lockForUpdate()->first();

            $known = $list->characters_list ?? [];
            $added = array_values(array_diff($chars, $known));

            if (empty($added)) {
                return ['added' => [], 'total' => count($known)];
            }

            if (count($known) + count($added) > $max) {
                return ['capped' => true, 'total' => count($known)];
            }

            // New characters append to the end: /characters renders the
            // list in insertion order, so additions read as newest-last.
            $merged = array_values(array_unique(array_merge($known, $added)));

            $list->update(['characters_list' => $merged]);

            // ─── PHASE 2 INSERTION POINT ──────────────────────────────
            // Premium daily promote cap + words table hook in here:
            //   - consult/increment a per-day promotion counter
            //   - write the promotion record
            // Both need a stored promotion (promoted_at or a words row),
            // which this pass deliberately does not add.
            // ──────────────────────────────────────────────────────────

            return ['added' => $added, 'total' => count($merged)];
        });

        if ($result['capped'] ?? false) {
            return response()->json([
                'error' => 'cap',
                'total' => $result['total'],
            ], 422);
        }

        return response()->json($result);
    }

    /**
     * Remove a saved word after confirming the caller owns it.
     */
    public function destroy(SavedWord $savedWord)
    {
        abort_unless($savedWord->user_id === auth()->id(), 403);   // ownership check
        $savedWord->delete();

        return response()->noContent();
    }
}
