@props(['theme' => 'dark', 'locked' => false])

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

    $chipBase = 'inline-flex items-center rounded-full border-2 px-2 py-1 text-[12px] font-semibold transition';

    // NOTE: keep these as complete literal class strings so Tailwind's
    // content scanner can see them (don't build them from fragments).
    $chipTheme = $theme === 'dark'
        ? 'border-white/40 bg-white/10 text-white hover:bg-white/20 peer-checked:border-white peer-checked:bg-white peer-checked:text-indigo-700 peer-checked:shadow-lg peer-focus-visible:ring-2 peer-focus-visible:ring-white'
        : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50 peer-checked:border-indigo-600 peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:shadow-lg peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-500';

    // Locked (free-user) treatment: dimmed, inert, no hover state.
    $chipLocked = $theme === 'dark'
        ? 'border-white/20 bg-white/5 text-white/45'
        : 'border-gray-200 bg-gray-50 text-gray-400';
@endphp

<fieldset {{ $attributes->merge(['class' => 'w-full']) }}>
    <legend class="block text-xs font-semibold uppercase tracking-[0.15em]">
        Theme &middot; 主题
    </legend>

    <p class="mt-1 text-xs {{ $helpClass }}">
        Pick one subject for this text, or leave it on Any.
    </p>

    <div class="mt-2 flex flex-wrap gap-1.5">
        {{-- Any = no theme; the value the generator treats as "unset".
             Stays live even when locked — it is the free experience. --}}
        <label class="cursor-pointer">
            <input type="radio" name="theme" value="" class="peer sr-only" checked>
            <span class="{{ $chipBase }} {{ $chipTheme }}">🎲 Any</span>
        </label>

        @foreach ($chips as $key => $chip)
            @if ($locked)
                <span class="group relative cursor-not-allowed select-none {{ $chipBase }} {{ $chipLocked }}">
                    {{ $chip['label'] }}
                    <span class="pointer-events-none absolute -top-9 left-1/2 z-20 -translate-x-1/2 whitespace-nowrap rounded-lg bg-[#1f2430] px-2.5 py-1.5 text-[10px] font-medium text-white opacity-0 shadow-xl transition group-hover:opacity-100">
                        Available with Premium ✨
                        <span class="absolute left-1/2 top-full -translate-x-1/2 border-4 border-transparent border-t-[#1f2430]"></span>
                    </span>
                </span>
            @else
                <label class="cursor-pointer">
                    <input type="radio" name="theme" value="{{ $key }}" class="peer sr-only">
                    <span class="{{ $chipBase }} {{ $chipTheme }}">{{ $chip['label'] }}</span>
                </label>
            @endif
        @endforeach
    </div>
</fieldset>
