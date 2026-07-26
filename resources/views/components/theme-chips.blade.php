@props(['theme' => 'dark'])

@php
    // NOTE ON THE WORD "THEME": $theme here is the VISUAL theme
    // (dark/light). The subject the learner picks is the radio field
    // named "theme" below. Two different layers that share a word.
    //
    // This component renders INSIDE the <x-advanced-options> box, so it
    // styles itself as a labelled field in that box (like Focus Words)
    // rather than as a standalone section with its own heading.

    $chips = config('topics.theme_chips', []);

    $helpClass = $theme === 'dark' ? 'text-white/70' : 'text-gray-500';

    $chipBase = 'inline-flex items-center rounded-full border-2 px-2 py-1 text-[14px] font-semibold transition';

    // NOTE: keep these as complete literal class strings so Tailwind's
    // content scanner can see them (don't build them from fragments).
    $chipTheme = $theme === 'dark'
        ? 'border-white/40 bg-white/10 text-white hover:bg-white/20 peer-checked:border-white peer-checked:bg-white peer-checked:text-indigo-700 peer-checked:shadow-lg peer-focus-visible:ring-2 peer-focus-visible:ring-white'
        : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50 peer-checked:border-indigo-600 peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:shadow-lg peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-500';
@endphp

<fieldset {{ $attributes->merge(['class' => 'w-full']) }}>
    <legend class="block text-xs font-semibold uppercase tracking-[0.15em]">
        Theme &middot; 主题
    </legend>

    <p class="mt-1 text-xs {{ $helpClass }}">
        Pick one subject for this text, or leave it on Any.
    </p>

    <div class="mt-2 flex flex-wrap gap-1.5">
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
