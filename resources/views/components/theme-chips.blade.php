@props(['theme' => 'dark'])

@php
    // NOTE ON THE WORD "THEME": $theme here is the VISUAL theme
    // (dark/light), matching <x-text-type-fieldset>. The subject the
    // learner picks is the radio field named "theme" below. Two different
    // layers that happen to share a word.

    $chips = config('topics.theme_chips', []);

    $legendClass = $theme === 'dark' ? 'text-white/80' : 'text-gray-500';
    $helpClass   = $theme === 'dark' ? 'text-white/60' : 'text-gray-400';

    $chipBase = 'inline-flex items-center rounded-full border-2 px-2 py-1 text-[12px] font-semibold transition';

    // NOTE: keep these as complete literal class strings so Tailwind's
    // content scanner can see them (don't build them from fragments).
    $chipTheme = $theme === 'dark'
        ? 'border-white/40 bg-white/10 text-white hover:bg-white/20 peer-checked:border-white peer-checked:bg-white peer-checked:text-indigo-700 peer-checked:shadow-lg peer-focus-visible:ring-2 peer-focus-visible:ring-white'
        : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50 peer-checked:border-indigo-600 peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:shadow-lg peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-500';
@endphp

<fieldset {{ $attributes->merge(['class' => 'w-72 max-w-full']) }}>
    <legend class="mb-2 block w-full text-center text-[11px] font-semibold uppercase tracking-[0.2em] {{ $legendClass }}">
        Pick a theme <span class="{{ $helpClass }}">· optional</span>
    </legend>

    <div class="flex flex-wrap justify-center gap-1.5">
        {{-- Any = no theme; the value the generator treats as "unset" --}}
        <label class="cursor-pointer">
            <input type="radio" name="theme" value="" class="peer sr-only" checked>
            <span class="{{ $chipBase }} {{ $chipTheme }}">🎲 Any</span>
        </label>

        @foreach ($chips as $key => $chip)
            <label class="cursor-pointer">
                <input type="radio" name="theme" value="{{ $key }}" class="peer sr-only">
                <span class="{{ $chipBase }} {{ $chipTheme }}">{{ $chip['label'] }}</span>
            </label>
        @endforeach
    </div>
</fieldset>
