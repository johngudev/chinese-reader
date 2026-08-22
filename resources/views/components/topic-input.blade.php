@props(['theme' => 'dark', 'locked' => false])

@php
    // Free-text topic for the next generation ("Describe the text").
    // Renders INSIDE <x-advanced-options>, in the slot theme-chips
    // occupied (above Focus Words), styled as a labelled field in that
    // box. Per-generation: never pre-filled from request or user state.
    //
    // $locked is ACCEPTED but the panel does not currently pass it —
    // the topic input is free for everyone (BRD 2026-08-22). Passing
    // :locked="true" renders the same premium-locked treatment as
    // Focus Words, should gating ever be wanted.

    // Input is always white with black text, regardless of the surrounding theme.
    // NOTE: keep these as complete literal class strings so Tailwind's
    // content scanner can see them (don't build them from fragments).
    $inputClass = 'border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500';

    $inputLocked = $theme === 'dark'
        ? 'border-white/20 bg-white/5 text-white/40 placeholder-white/30'
        : 'border-gray-200 bg-gray-50 text-gray-400 placeholder-gray-300';

    $diceClass = $theme === 'dark'
        ? 'border-white/40 bg-white/10 text-white hover:bg-white/20'
        : 'border-gray-300 bg-white text-gray-700 hover:border-indigo-400';

    $diceLocked = $theme === 'dark'
        ? 'border-white/20 bg-white/5 text-white/45'
        : 'border-gray-200 bg-gray-50 text-gray-400';

    $helpClass = $theme === 'dark' ? 'text-white/70' : 'text-gray-500';

    // Dice examples — hardcoded by design (no config entry).
    $diceTopics = [
        // Everyday situations
        'at the airport',
        'at the train station',
        'at the restaurant',
        'at the hospital',
        'at the bank',
        'at the post office',
        'at the supermarket',
        'at the hotel',
        'at the library',
        'at the barber shop',
        'at the bus stop',
        'at the night market',
        'at the doctor\'s office',
        'at a tea house',
        'at the gym',

        // My life
        'my typical school day',
        'my typical workday',
        'my family',
        'my hometown',
        'my best friend',
        'my daily routine',
        'my favorite food',
        'my weekend',
        'my neighborhood',
        'my apartment',
        'my hobbies',
        'my pet',
        'my morning routine',
        'my favorite season',
        'my plans for the future',

        // Practical situations
        'ordering food at a restaurant',
        'asking for directions',
        'buying train tickets',
        'seeing a doctor',
        'shopping for clothes',
        'taking a taxi',
        'renting an apartment',
        'opening a bank account',
        'sending a package',
        'planning a trip',
        'meeting someone new',
        'introducing yourself',
        'inviting a friend to dinner',
        'making a phone call',
        'apologizing for being late',

        // Stories
        'a story about a dog and a cat',
        'an animal folk tale',
        'a story about two friends',
        'a story about a family dinner',
        'a story about losing something',
        'a story about a long journey',
        'a story about a misunderstanding',
        'a story about helping a stranger',
        'a story about a child and a grandparent',
        'a story about moving to a new city',
        'a folk tale about a clever rabbit',
        'a story about a farmer',
        'a story about a fisherman',
        'a story about a rainy day',
        'a story with a happy ending',

        // General subjects
        'religion',
        'chinese history',
        'chinese food',
        'chinese festivals',
        'sports',
        'music',
        'movies',
        'travel',
        'the weather',
        'the four seasons',
        'school life',
        'city life',
        'life in the countryside',
        'family life',
        'friendship',
        'health',
        'exercise',
        'work',
        'money',
        'shopping',
        'cooking',
        'transportation',
        'technology',
        'the internet',
        'nature',
        'animals',
        'education',
        'holidays',
        'childhood memories',
        'growing old',

        // Chinese culture
        'chinese new year',
        'the mid-autumn festival',
        'the dragon boat festival',
        'making dumplings',
        'drinking tea',
        'calligraphy',
        'tai chi in the park',
        'a traditional wedding',
        'the zodiac animals',
        'eating hot pot with friends',

        // Conversations
        'a conversation between a teacher and a student',
        'a conversation between a doctor and a patient',
        'a conversation between two neighbors',
        'a conversation at a job interview',
        'a phone call between old friends',
        'a conversation between a shopkeeper and a customer',

        // School & work
        'the first day of school',
        'a difficult exam',
        'learning chinese',
        'doing homework',
        'a busy day at work',
        'a new coworker',
        'a class trip',
        'a school sports day',

        // Daily life
        'taking the subway',
        'a walk in the park',
        'doing housework',
        'cooking dinner',
        'watching tv at night',
        'waking up early',
        'riding a bicycle',
        'waiting in line',
        'a day without internet',
        'going to the market',

        // Nature & weather
        'a rainy day',
        'a snowy day',
        'a hot summer day',
        'spring in the park',
        'autumn leaves',

        // Travel
        'a trip to beijing',
        'a trip to shanghai',
        'visiting the countryside',
        'a vacation at the beach',
        'climbing a mountain',
        'visiting a temple',
        'a trip to see family',

        // Life events
        'a birthday party',
        'a wedding',
        'moving to a new house',
        'learning to drive',
        'keeping a pet',
        'growing vegetables',
        'reading books',
        'writing a letter to a friend',
        'an old photograph',
        'good news and bad news',
    ];

    $placeholder = 'e.g. two friends at a restaurant, a funny story about a cat, daily life in Beijing';
@endphp

<div {{ $attributes->merge(['class' => 'w-full mt-5']) }} data-topic-input>
    <label @unless($locked) for="topic" @endunless class="mt-2 block text-xs font-semibold uppercase tracking-[0.15em]">
        Topic &middot; 主题
    </label>
    <p class="mt-1 text-xs {{ $helpClass }}">
        Describe the text you want to generate.
    </p>
    @if ($locked)
        <div class="group relative mt-2 flex w-full items-center gap-2">
            <input type="text" disabled maxlength="160"
                placeholder="{{ $placeholder }}"
                class="pointer-events-none block w-full cursor-not-allowed rounded-lg text-sm shadow-sm {{ $inputLocked }}">
            <span class="shrink-0 cursor-not-allowed select-none rounded-lg border-2 px-3 py-2 text-lg {{ $diceLocked }}">🎲</span>
            {{-- Same -top-9 offset as every other tooltip in the panel. --}}
            <span class="pointer-events-none absolute -top-9 left-1/2 z-20 -translate-x-1/2 whitespace-nowrap rounded-lg bg-[#1f2430] px-2.5 py-1.5 text-[10px] font-medium text-white opacity-0 shadow-xl transition group-hover:opacity-100">
                Available with Premium ✨
                <span class="absolute left-1/2 top-full -translate-x-1/2 border-4 border-transparent border-t-[#1f2430]"></span>
            </span>
        </div>
    @else
        <div class="mt-2 flex w-full items-center gap-2">
            <input type="text" name="topic" id="topic" maxlength="160"
                placeholder="{{ $placeholder }}"
                class="block w-full rounded-lg text-sm shadow-sm {{ $inputClass }}">
            <button type="button" title="Surprise me"
                class="js-topic-dice shrink-0 rounded-lg border-2 px-3 py-2 text-lg transition {{ $diceClass }}"
                data-topics='@json($diceTopics)'>🎲</button>
        </div>
    @endif
</div>

@once
    <script>
        // One delegated handler no matter how many instances render
        // (story.blade.php declares the panel in two branches).
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.js-topic-dice');
            if (! btn) return;

            const wrap  = btn.closest('[data-topic-input]');
            const input = wrap?.querySelector('input[name="topic"]');
            if (! input) return;

            const topics = JSON.parse(btn.dataset.topics || '[]');
            input.value = topics[Math.floor(Math.random() * topics.length)] ?? '';
            input.focus();
        });
    </script>
@endonce
