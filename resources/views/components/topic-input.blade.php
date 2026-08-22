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
        'two friends ordering dinner at a restaurant',
        'a funny story about a lazy cat',
        'daily life in Beijing',
        "a student's first day of school",
        'buying fruit at the market',
        'a family trip by train',
        'the weather this week',
        "my best friend's birthday party",
        'a lost phone and a kind stranger',
        'morning exercise in the park',
        'a conversation between a taxi driver and a tourist',
        'making dumplings with grandma',
        'a small dog who loves the rain',
        'shopping for a gift',
        'an email to an old teacher',
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
