@props(['theme' => 'dark', 'locked' => false])

@php
    $options = [
        '' => 'Default',
        'story' => 'Story',
        'article' => 'Article',
        'dialogue' => 'Dialogue',
    ];

    $legendClass = $theme === 'dark' ? 'text-white/80' : 'text-gray-500';
    $helpClass   = $theme === 'dark' ? 'text-white/60' : 'text-gray-400';

    $pillBase = 'flex items-center justify-center rounded-xl border-2 px-4 py-2.5 text-sm font-semibold shadow-sm transition';

    // NOTE: keep these as complete literal class strings so Tailwind's
    // content scanner can see them (don't build them from fragments).
    $pillTheme = $theme === 'dark'
        ? 'border-white/40 bg-white/10 text-white hover:bg-white/20 peer-checked:border-white peer-checked:bg-white peer-checked:text-indigo-700 peer-checked:shadow-lg peer-focus-visible:ring-2 peer-focus-visible:ring-white'
        : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50 peer-checked:border-indigo-600 peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:shadow-lg peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-500';

    // Locked (free-user) treatment: dimmed, inert, no hover state.
    $pillLocked = $theme === 'dark'
        ? 'border-white/20 bg-white/5 text-white/45'
        : 'border-gray-200 bg-gray-50 text-gray-400';
@endphp

<fieldset {{ $attributes->merge(['class' => 'w-72 max-w-full']) }}>
    <legend class="mb-2 block w-full text-center text-[11px] font-semibold uppercase tracking-[0.2em] {{ $legendClass }}">
        Choose your text type
    </legend>

    <div class="grid grid-cols-2 gap-2">
        @foreach ($options as $value => $label)
            @if ($locked && $value !== '')
                <span class="group relative cursor-not-allowed select-none {{ $pillBase }} {{ $pillLocked }}">
                    {{ $label }}
                    <span class="pointer-events-none absolute -top-9 left-1/2 z-20 -translate-x-1/2 whitespace-nowrap rounded-lg bg-[#1f2430] px-2.5 py-1.5 text-[10px] font-medium text-white opacity-0 shadow-xl transition group-hover:opacity-100">
                        Available with Premium ✨
                        <span class="absolute left-1/2 top-full -translate-x-1/2 border-4 border-transparent border-t-[#1f2430]"></span>
                    </span>
                </span>
            @else
                {{-- Default falls through here even when locked: it stays a
                     real checked radio, because it is the free experience. --}}
                <label class="cursor-pointer">
                    <input type="radio" name="variety" value="{{ $value }}" class="peer sr-only" @checked($value === '')>
                    <span class="{{ $pillBase }} {{ $pillTheme }}">
                        {{ $label }}
                    </span>
                </label>
            @endif
        @endforeach
    </div>

    @if ($locked)
        {{-- No 🔒 here: with the panel open there would be three padlocks
             in ~300px (this line, the summary badge, the panel note). The
             sentence carries the message; the icons live on the panel. --}}
        <p class="mt-1.5 text-center text-[11px] leading-snug {{ $helpClass }}">
            Free texts use <strong class="font-semibold">Default</strong> — other styles are Premium.
        </p>
    @endif
</fieldset>
