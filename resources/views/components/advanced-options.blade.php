@props(['theme' => 'dark'])

@php
    $legendClass = $theme === 'dark' ? 'text-white/80' : 'text-gray-500';

    // NOTE: keep these as complete literal class strings so Tailwind's
    // content scanner can see them (don't build them from fragments).
    $boxClass = $theme === 'dark'
        ? 'border-white/30 bg-white/10 text-white'
        : 'border-gray-200 bg-white text-gray-700';

    $inputClass = $theme === 'dark'
        ? 'border-white/40 bg-white/10 text-white placeholder-white/40 focus:border-white focus:ring-white'
        : 'border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500';

    $helpClass = $theme === 'dark' ? 'text-white/70' : 'text-gray-500';
@endphp

<details {{ $attributes->merge(['class' => 'w-72 max-w-full']) }}>
    <summary class="cursor-pointer select-none text-center text-[11px] font-semibold uppercase tracking-[0.2em] {{ $legendClass }}">
        Advanced &#9662;
    </summary>

    <div class="mt-2 rounded-xl border-2 px-4 py-3 text-left {{ $boxClass }}">
        <label for="focus_words" class="block text-xs font-semibold uppercase tracking-[0.15em]">
            Focus Words &middot; 词
        </label>
        <p class="mt-1 text-xs {{ $helpClass }}">
            Enter up to 5 words (comma separated) and some of those words will show up in your text.
        </p>
        <input type="text" name="focus_words" id="focus_words"
            value="{{ request('focus_words') }}"
            placeholder="学习, 朋友, 天气"
            class="mt-2 w-full rounded-lg text-sm shadow-sm {{ $inputClass }}">
    </div>
</details>
