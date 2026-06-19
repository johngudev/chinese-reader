<?php

use App\Models\GeneratedText;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

if (! function_exists('getDefinitions')) {
    function getDefinitions(string $chinese, int $maxLen = 8): array
    {
        $chars = preg_split('//u', $chinese, -1, PREG_SPLIT_NO_EMPTY);   // ← every char, punctuation included
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
}

if (! function_exists('getStoryFromAnthropic')) {
    function getStoryFromAnthropic($userId, $characters, $variety = null) {

        $char_diversity_note = "";

        // Premium: explicit style choice
        $styleMap = [
            'news'     => ' The text should resemble a news story (you may include well-known proper nouns like America, China, Japan).',
            'article'  => ' The text should resemble an informational/encyclopedia-style article rather than a narrative.',
            'story'    => ' The text should be a short narrative story.',
            'dialogue' => ' The text should be a short, natural dialogue between two people.',
        ];

        if ($variety && isset($styleMap[$variety])) {
            $char_diversity_note .= $styleMap[$variety];
        }

        if (count($characters) > 500) {
            //Character diversity for over 500 characters
            if (rand(1, 100) <= 40) {
                $char_diversity_note .= " When creating your response, focus on using characters that are more rare in the Chinese language, as this will help me learn more. Also make each text you generate diverse, covering a wide array of contexts, subject matters, styles (fiction, nonfiction, narrative, essay), and so on.";
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

        // save the story to the database
        $generated = GeneratedText::create([
            'user_id' => $userId,
            'prompt' => "Characters I know: {$charList}\n\nWrite me a text using only these characters. {$char_diversity_note}",
            'generated_text' => $response->json('content.0.text'),
        ]);

        return ['story' => $story, 'charList' => $charList, 'char_diversity_note' => $char_diversity_note, 'generated' => $generated];
    }
}
