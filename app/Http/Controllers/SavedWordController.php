<?php

namespace App\Http\Controllers;

use App\Models\SavedWord;
use Illuminate\Http\Request;

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
        $words = auth()->user()->savedWords()->latest()->paginate(10, ['id', 'generated_text_id', 'word', 'pinyin', 'english']);

        // If the user is not premium, lock the list after page 1
        $locked = ! auth()->user()->isPremium() && $words->currentPage() >= 2;

        // Locked pages only show a 5-word teaser behind the blur
        if ($locked) {
            $words->setCollection($words->getCollection()->take(5));
        }

        return view('saved-words', ['words' => $words, 'locked' => $locked]);
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
     * Remove a saved word after confirming the caller owns it.
     */
    public function destroy(SavedWord $savedWord)
    {
        abort_unless($savedWord->user_id === auth()->id(), 403);   // ownership check
        $savedWord->delete();

        return response()->noContent();
    }
}
