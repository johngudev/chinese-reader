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

    // Dice examples — hardcoded by design (no config entry).
    $diceTopics = [
        // Food & drink
        'two friends ordering dinner at a restaurant',
        'buying fruit at the market',
        'making dumplings with grandma',
        'a cook who puts too much salt in everything',
        'trying a strange new dish for the first time',
        'the best noodle shop in town',
        'a picnic that gets interrupted by rain',
        'drinking tea with an old friend',
        'a child who refuses to eat vegetables',
        'learning to cook from an online video',
        'a birthday cake that goes wrong',
        'street food on a cold evening',
        'two coworkers arguing about where to eat lunch',
        'a famous restaurant with only one table',
        'forgetting the rice on the stove',

        // Family & friends
        "my best friend's birthday party",
        'a phone call home to mom',
        'two brothers who want the same toy',
        'a grandmother teaching her grandson to write characters',
        'visiting relatives during the holidays',
        'an argument between friends that ends with laughter',
        'a family photo where nobody smiles',
        'the day my little sister was born',
        'helping dad fix something in the house',
        'a letter to a friend who moved away',
        'meeting an old classmate on the street',
        'a surprise visit from an uncle',
        'two friends who always argue about basketball',
        'a quiet dinner with the whole family',
        'teaching grandpa to use a smartphone',

        // Daily life
        'daily life in Beijing',
        'morning exercise in the park',
        'a very long line at the bank',
        'losing my keys before work',
        'the neighbor who sings too loudly',
        'a day with no phone',
        'cleaning the house before guests arrive',
        'an elevator that stops between floors',
        'the first cup of coffee in the morning',
        'a walk home in the snow',
        'waiting for a package that never comes',
        'a barber who talks too much',
        'the small shop at the corner of my street',
        'doing laundry on a sunny day',
        'a power cut during dinner',

        // School & work
        "a student's first day of school",
        'an email to an old teacher',
        'a test that everyone fails',
        'the classmate who always sleeps in class',
        'my first day at a new job',
        'a boss who forgets everything',
        'studying late at night for an exam',
        'a school trip to the museum',
        'the teacher who never smiles',
        'a meeting that could have been an email',
        'losing homework on the bus',
        'a student who asks too many questions',
        'the last day of school before summer',
        'learning a new language',
        'a job interview that goes strangely well',

        // Travel & places
        'a family trip by train',
        'a conversation between a taxi driver and a tourist',
        'getting lost in a new city',
        'the view from the top of a mountain',
        'a small town by the sea',
        'missing the last bus home',
        'an airplane seat between two strangers',
        'a hotel room with a secret door',
        'asking a stranger for directions',
        'a boat ride on a quiet lake',
        'the night market in summer',
        'visiting the Great Wall for the first time',
        'a road trip with too much luggage',
        'an old bridge and its story',
        'the wrong train to the right place',

        // Animals & nature
        'a funny story about a lazy cat',
        'a small dog who loves the rain',
        'a bird that visits my window every morning',
        'the fish that grew too big for its tank',
        'a panda who refuses to get up',
        'walking a very stubborn dog',
        'a cat and a dog who become friends',
        'the oldest tree in the park',
        'a rabbit who escapes from its cage',
        'feeding ducks by the lake',
        'a spider in the corner of the bathroom',
        'a horse that only walks slowly',
        'flowers blooming in spring',
        'a mouse living in the kitchen',
        'a turtle who wins a race',

        // Weather & seasons
        'the weather this week',
        'the first snow of winter',
        'a typhoon day spent at home',
        'the hottest day of summer',
        'autumn leaves in the old park',
        'forgetting an umbrella on a rainy day',
        'a fog so thick you cannot see the road',
        'spring cleaning and what was found',
        'a thunderstorm during the night',
        'the perfect day for flying kites',

        // Stories & whimsy
        'a lost phone and a kind stranger',
        'a door that opens to yesterday',
        'the man who never takes off his hat',
        'a letter found inside an old book',
        'the shop that sells only red things',
        'a child who can talk to fish',
        'the clock that runs backwards',
        'a dream that comes true the next day',
        'the umbrella that brings good luck',
        'a robot who wants to learn to cook',
        'the painting that changes at night',
        'a king who is afraid of the dark',
        'the last lamp on the street',
        'a magic pen that writes by itself',
        'the girl who collects lost buttons',

        // Shopping & money
        'shopping for a gift',
        'buying shoes that are too small',
        'a wallet found on the sidewalk',
        'bargaining at the flea market',
        'saving money for something special',
        'an online order that arrives wrong',
        'the most expensive thing in the store',
        'a free sample that changes everything',
        'two people who want the last one on the shelf',
        'returning a gift without hurting feelings',

        // Health & sports
        'a morning run that goes too far',
        'learning to swim as an adult',
        'a basketball game in the rain',
        'the doctor who gives strange advice',
        'trying yoga for the first time',
        'a bicycle ride around the city',
        'catching a cold before a big day',
        'the oldest runner in the race',
        'a table tennis match between friends',
        'falling asleep during a massage',

        // Technology & modern life
        'a phone that keeps calling the wrong person',
        'the day the internet stopped working',
        'an old computer with important photos inside',
        'a video call with someone far away',
        'too many passwords to remember',
        'a smart speaker that misunderstands everything',
        'the last person in town without a phone',
        'an app that promises too much',
        'taking the perfect photo of dinner',
        'a text message sent to the wrong person',

        // Culture & celebrations
        'preparing for Chinese New Year',
        'making lanterns for the festival',
        'a wedding where everyone dances',
        'the story behind a family recipe',
        'watching fireworks from the roof',
        'a moon cake with a surprise inside',
        'learning calligraphy from a master',
        'the lion dance in the street',
        'a gift exchange that goes in circles',
        'writing wishes for the new year',
    ];

    $placeholder = 'e.g. two friends at a restaurant, a funny story about a cat, daily life in Beijing';
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }} data-topic-input>
    <label @unless($locked) for="topic" @endunless class="block text-xs font-semibold uppercase tracking-[0.15em]">
        Describe the text &middot; 主题
    </label>

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
