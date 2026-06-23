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

        $topics = [
            "Donald Trump",
            "Joe Biden",
            "Kamala Harris",
            "J.D. Vance",
            "Xi Jinping",
            "Vladimir Putin",
            "Volodymyr Zelenskyy",
            "Benjamin Netanyahu",
            "Masoud Pezeshkian",
            "Narendra Modi",
            "Keir Starmer",
            "Emmanuel Macron",
            "Friedrich Merz",
            "Giorgia Meloni",
            "Ursula von der Leyen",
            "Mark Carney",
            "Claudia Sheinbaum",
            "Lula da Silva",
            "Cyril Ramaphosa",
            "Mohammed bin Salman",

            "Recep Tayyip Erdogan",
            "Pope Leo XIV",
            "Greta Thunberg",
            "Malala Yousafzai",
            "Alexei Navalny",
            "Aung San Suu Kyi",
            "Ai Weiwei",
            "Banksy",
            "Yayoi Kusama",
            "Kara Walker",
            "Ta-Nehisi Coates",
            "Salman Rushdie",
            "Margaret Atwood",
            "Haruki Murakami",
            "Chimamanda Ngozi Adichie",
            "Taylor Swift",
            "Beyoncé",
            "Kendrick Lamar",
            "Bad Bunny",
            "Billie Eilish",

            "Sabrina Carpenter",
            "BTS",
            "Blackpink",
            "Lionel Messi",
            "Cristiano Ronaldo",
            "Kylian Mbappé",
            "Shohei Ohtani",
            "Caitlin Clark",
            "Simone Biles",
            "LeBron James",
            "Serena Williams",
            "Coco Gauff",
            "Naomi Osaka",
            "Christopher Nolan",
            "Greta Gerwig",
            "Steven Spielberg",
            "Hayao Miyazaki",
            "Bong Joon-ho",
            "Zendaya",
            "Timothée Chalamet",

            "Sam Altman",
            "Elon Musk",
            "Tim Cook",
            "Jensen Huang",
            "Satya Nadella",
            "Sundar Pichai",
            "Mark Zuckerberg",
            "Jeff Bezos",
            "Lisa Su",
            "Demis Hassabis",
            "Jane Goodall",
            "Jennifer Doudna",
            "Katalin Karikó",
            "Geoffrey Hinton",
            "Yann LeCun",
            "Fei-Fei Li",
            "Neil deGrasse Tyson",
            "Brian Cox",
            "Anthony Fauci",
            "Atul Gawande",

            "OpenAI",
            "Google",
            "Google DeepMind",
            "Microsoft",
            "Apple",
            "Meta",
            "Nvidia",
            "Amazon",
            "Tesla",
            "SpaceX",
            "TikTok",
            "Samsung",
            "Sony",
            "Netflix",
            "Disney",
            "Nintendo",
            "Spotify",
            "YouTube",
            "X",
            "Reddit",

            "Toyota",
            "Volkswagen",
            "Ford",
            "General Motors",
            "Boeing",
            "Airbus",
            "Delta Air Lines",
            "United Airlines",
            "Walmart",
            "Target",
            "Costco",
            "Home Depot",
            "McDonald's",
            "Starbucks",
            "Coca-Cola",
            "PepsiCo",
            "Nike",
            "Adidas",
            "LVMH",
            "IKEA",

            "Pfizer",
            "Moderna",
            "Johnson & Johnson",
            "Novo Nordisk",
            "Eli Lilly",
            "JPMorgan Chase",
            "Goldman Sachs",
            "BlackRock",
            "Visa",
            "Mastercard",
            "ExxonMobil",
            "Shell",
            "BP",
            "Chevron",
            "BYD",
            "Honda",
            "Hyundai",
            "Lego",
            "Unilever",
            "Nestlé",

            "United States",
            "China",
            "India",
            "Russia",
            "Ukraine",
            "Israel",
            "Iran",
            "Saudi Arabia",
            "United Kingdom",
            "France",
            "Germany",
            "Japan",
            "South Korea",
            "Taiwan",
            "Mexico",
            "Canada",
            "Brazil",
            "Turkey",
            "Egypt",
            "South Africa",

            "Nigeria",
            "Indonesia",
            "Vietnam",
            "Thailand",
            "Singapore",
            "Australia",
            "New Zealand",
            "Poland",
            "Spain",
            "Italy",
            "Netherlands",
            "Pakistan",
            "Bangladesh",
            "Qatar",
            "United Arab Emirates",
            "Syria",
            "Lebanon",
            "Jordan",
            "Iraq",
            "Yemen",

            "Gaza",
            "West Bank",
            "Hong Kong",
            "Beijing",
            "Shanghai",
            "Taipei",
            "Tokyo",
            "Seoul",
            "New Delhi",
            "Mumbai",
            "London",
            "Paris",
            "Berlin",
            "Brussels",
            "Moscow",
            "Kyiv",
            "Jerusalem",
            "Dubai",
            "New York City",
            "Los Angeles",

            "Houston",
            "Washington D.C.",
            "San Francisco",
            "Chicago",
            "Mexico City",
            "São Paulo",
            "Rio de Janeiro",
            "Cairo",
            "Lagos",
            "Johannesburg",
            "Nairobi",
            "Istanbul",
            "Bangkok",
            "Singapore",
            "Sydney",
            "Melbourne",
            "Toronto",
            "Vancouver",
            "Madrid",
            "Rome",

            "United Nations",
            "NATO",
            "European Union",
            "G7",
            "G20",
            "World Health Organization",
            "International Monetary Fund",
            "World Bank",
            "World Trade Organization",
            "FIFA",
            "International Olympic Committee",
            "NBA",
            "WNBA",
            "NFL",
            "MLB",
            "Formula 1",
            "OPEC",
            "BRICS",
            "ASEAN",
            "African Union",

            "NASA",
            "European Space Agency",
            "International Criminal Court",
            "International Court of Justice",
            "Federal Reserve",
            "SEC",
            "FDA",
            "Supreme Court of the United States",
            "U.S. Congress",
            "Chinese Communist Party",
            "European Commission",
            "Harvard University",
            "Stanford University",
            "MIT",
            "Oxford University",
            "Cambridge University",
            "Tsinghua University",
            "Peking University",
            "University of Tokyo",
            "World Cup",
            "Olympics",
        ];

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

    $systemMessage = 'You help people practice reading Chinese. Using ONLY the characters the user lists '
        . '(one or two characters outside the set is OK, keep it minimal), write a coherent text in '
        . 'Simplified Chinese, 80–120 characters, purely in Chinese characters. Follow the creative brief '
        . 'in the user message for THIS text. After the Chinese text, output an <hr> and then an English '
        . 'translation. If you add a title, make it the first sentence of the text (no extra linebreaks or '
        . 'tags). Write naturally for a learner and avoid generic filler (e.g., an unnamed student simply going to school).';


        //Diversity
        
        if(count($characters) < 200) {
            $topics = config('topics.easy_topics');
        } else {
            $topics = config('topics.topics');
        }
        $tones = config('topics.tones');
        $forms = config('topics.forms');
        //

        //select random from each
        $topic = $topics[array_rand($topics)];
        $tone = $tones[array_rand($tones)];
        $form = $forms[array_rand($forms)];

        //do a special request 80% of the time
        if (rand(1, 100) <= 80) {
            if(is_null($variety)) {
                $char_diversity_note .= "The form of THIS text should follow the structure of {$form}.  The tone of the text should be {$tone}. For THIS text, make the story about {$topic} or feature {$topic} in some way.";
            }

            if($variety) {
                $char_diversity_note .= "The tone of THIS text should be {$tone}.";
            }
        }

        //throttle request to max first 1,0000 characters

        $response = Http::withHeaders([
            'x-api-key'         => env('X_API_KEY'),
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
            'model'      => 'claude-haiku-4-5-20251001',
            'max_tokens' => 2000,
            'system'     => $systemMessage,
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
