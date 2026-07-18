<?php

use App\Models\GeneratedText;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;



if (! function_exists('getDefinitions')) {
    function getDefinitions(string $chinese, ?array $charactersList = null, int $maxLen = 8): array
    {
        $chars = preg_split('//u', $chinese, -1, PREG_SPLIT_NO_EMPTY);   // ← every char, punctuation included
        $n     = count($chars);

        if ($n === 0) return [];

        //if a list of characters is given, mark words that contain new characters for the user
        if ($charactersList) {
            // Known characters as a hash set; null ⇒ no list given, flagging off
            $known = $charactersList ? array_flip($charactersList) : null;        
        } else {
            $known = null;
        }


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

                // does this word contain at least one Han char outside the user's list?
                $hasNewChar = false;
                if ($known !== null) {
                    foreach (array_slice($chars, $i, $len) as $c) {
                        if (preg_match('/\p{Han}/u', $c) && ! isset($known[$c])) {
                            $hasNewChar = true;
                            break;
                        }
                    }
                }

                $definitions[] = [
                    'word'    => $word,
                    'entries' => $dict[$word]->map(fn ($e) => [
                        'pinyin'         => $e->pinyin,
                        'pinyin_numeric' => $e->pinyin_numeric,
                        'english'        => $e->english,
                    ])->all(),
                    'new_character_flag' => $hasNewChar,
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
    function getStoryFromAnthropic($userId, $characters, $variety = null, array $focusWords = []) {

        $char_diversity_note = "";

        if($variety == 'surprise') {
            $variety = ['news', 'article', 'story', 'dialogue'][rand(0, 3)];
        }

        // Premium: explicit style choice
        $styleMap = [
            'news'     => ' The text should resemble a news story. When creating your response, go out and think of actual current news from the last year or so.  Because the Chinese portion of the text is meant to be geared towards learners, you MUST use english when referencing proper nouns like nations, and names of people, the names of companies and organizations, and even event names like "World Cup". Keep the news varied and focus on big news events.  This can include news about for example prominent figures in music, culture, politics, busines, technology and other prominent figures and events in various fields.',
            'article'  => ' The text should resemble an informational/encyclopedia-style article rather than a narrative.',
            'story'    => ' The text should be a short narrative story.',
            'dialogue' => ' The text should be a short, natural dialogue between two people. No need to narration like: "He said, XYZ" or "XYZ, she responded."  Just have the words that are spoken between the interlocutors, separated by linebreaks. ' ,
        ];

        if ($variety && isset($styleMap[$variety])) {
            $char_diversity_note .= $styleMap[$variety];
        }

        $topics = config('topics.news_topics');

        if ($variety === 'news') {
            $char_diversity_note .= " Do not mention Taylor Swift in any way. You may include this topic: {$topics[array_rand($topics)]}.";
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

            if(!isset($styleMap[$variety])) {
                //provides variety when does not exist

                if ($text_type_number <= 40) {
                    $char_diversity_note = $char_diversity_note . " The text should resemble a news story (you may include well-known proper nouns, like America, England, China, Japan, etc., instead of a narrative.";
                } else if($text_type_number < 70)  {
                    $char_diversity_note = $char_diversity_note . " The text should resemble an information article, such as Encyclopedia entry, rather than a narrative.";
                }
            }

        }

        $charList = implode(' ', $characters);

        if(count($characters) < 200) {
            $passage_length = "60-80"; 
        }  else {
            $passage_length = "80-100";
        }

    $systemMessage = 'You help people practice reading Chinese. Using ONLY the characters the user lists. '
        . ' Adherence level to using ONLY characters in the user list = MAXIMUM.  YOU ARE FORBIDDEN FROM USING ANY CHARACTERS NOT IN THE USER LIST.  '
        . ' Write a coherent text in '
        . 'Simplified Chinese, ' . $passage_length . ' characters, purely in Chinese characters. Follow the creative brief '
        . 'in the user message for THIS text. After the Chinese text, output an <hr> and then an English '
        . 'translation. (Note that the separator between the Chinese and English text must be an <hr>)  If you add a title, make it the first sentence of the text (no extra linebreaks or '
        . 'tags). Here is the list of characters the user knows: [' . $charList . ']';

        // Focus words: user-chosen words that MUST appear; their characters are
        // permitted even if outside the known-characters list.
        if (! empty($focusWords)) {
            $focusList = implode('、', $focusWords);

            $systemMessage .= ' FOCUS WORDS EXCEPTION: the user has chosen these focus words: ['
                . $focusList . ']. You MUST use at least one of them in the Chinese text, and their '
                . 'characters are permitted even if they are not in the character list above.';

            $char_diversity_note .= " IMPORTANT: You MUST use at least one of these focus words"
                . " in the Chinese text: {$focusList}.";
        }


        //Diversity
        
        if(count($characters) < 200) {
            $topics = config('topics.easy_topics');
        } else {
            $topics = config('topics.topics');
        }

        $tones = config('topics.tones');

        if(count($characters) < 200) {
            $forms = config('topics.easy_forms');
        } else {
            $forms = config('topics.forms');
        }
        //

        //select random from each
        $topic = $topics[array_rand($topics)];
        $tone = $tones[array_rand($tones)];
        $form = $forms[array_rand($forms)];

        //do a special request 80% of the time
            $diversityChance = min(75, max(25, count($characters) / 15));

        if (rand(1, 100) <= $diversityChance) {
            if(is_null($variety)) {
                $char_diversity_note .= "The form of THIS text should follow the structure of {$form}.  The tone of the text should be {$tone}. For THIS text, perhaps you could amke the story about {$topic} or feature {$topic} in some way (this is just a suggestion, mainly focus on using the characters the user knows).";
            }

            if($variety) {
                $char_diversity_note .= "The tone of THIS text should be {$tone}.";
            }
        }

        $char_diversity_note .= " FINAL RULE, overriding every suggestion above: if the suggested topic, form, or style cannot be expressed using ONLY the listed characters, ignore that suggestion and write about something the characters CAN express. The character list always wins.";


        //throttle request to max first 1,0000 characters

        $apiStart = microtime(true);

        $response = Http::withHeaders([
            'x-api-key'         => env('X_API_KEY'),
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
            'model'      => config('anthropic.model'),
            'max_tokens' => 2000,
            'system'     => $systemMessage,
            'messages'   => [
                ['role' => 'user', 'content' => "Characters I know: {$charList}\n\nWrite me a text using ONLY the characters I know. " . $char_diversity_note],
            ],
        ]);

        $apiDuration = microtime(true) - $apiStart;

        // dd($response->json());

        $story = $response->json('content.0.text');

        // save the story to the database
        $generated = GeneratedText::create([
            'user_id' => $userId,
            'prompt' => "Characters I know: {$charList}\n\nWrite me a text using only these characters. {$char_diversity_note}",
            'generated_text' => $response->json('content.0.text'),
        ]);

        return ['story' => $story, 'charList' => $charList, 'char_diversity_note' => $char_diversity_note, 'generated' => $generated, 'api_duration' => $apiDuration];
    }
}

if (! function_exists('countryFromTimezone')) {
    /**
     * Convert an IANA timezone (e.g. "America/Chicago") to an
     * ISO 3166-1 alpha-2 country code (e.g. "US"), or null.
     */
    function countryFromTimezone(?string $timezone): ?string
    {
        if (! $timezone) {
            return null;
        }

        try {
            $location = (new DateTimeZone($timezone))->getLocation();
        } catch (Exception $e) {
            return null;
        }

        $code = $location['country_code'] ?? null;

        return ($code && $code !== '??') ? $code : null;
    }
}

if (! function_exists('freeDailyGenerationCap')) {
    /** Texts a non-premium user may generate per calendar day. */
    function freeDailyGenerationCap(): int
    {
        return (int) config('app.free_daily_generation_cap', 4);
    }
}

if (! function_exists('generationsUsedToday')) {
    /** Texts the given user has generated since local midnight. */
    function generationsUsedToday($user): int
    {
        return $user->generatedTexts()
            ->where('created_at', '>=', now()->startOfDay())
            ->count();
    }
}