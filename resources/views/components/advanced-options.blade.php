@props(['theme' => 'dark', 'locked' => false])

@php
    $legendClass = $theme === 'dark' ? 'text-white/80' : 'text-gray-500';

    // NOTE: keep these as complete literal class strings so Tailwind's
    // content scanner can see them (don't build them from fragments).
    $boxClass = $theme === 'dark'
        ? 'border-white/30 bg-white/10 text-white'
        : 'border-gray-200 bg-white text-gray-700';

    // Input is always white with black text, regardless of the surrounding theme.
    $inputClass = 'border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:border-indigo-500 focus:ring-indigo-500';

    $inputLocked = $theme === 'dark'
        ? 'border-white/20 bg-white/5 text-white/40 placeholder-white/30'
        : 'border-gray-200 bg-gray-50 text-gray-400 placeholder-gray-300';

    $helpClass = $theme === 'dark' ? 'text-white/70' : 'text-gray-500';

    $ruleClass = $theme === 'dark' ? 'bg-white/20' : 'bg-gray-200';

    $badgeClass = $theme === 'dark' ? 'bg-white/15 text-white/90' : 'bg-indigo-50 text-indigo-600';

    $noteClass = $theme === 'dark'
        ? 'bg-white/10 ring-white/20'
        : 'bg-indigo-50 text-indigo-900 ring-indigo-100';

    $noteLinkClass = $theme === 'dark'
        ? 'underline decoration-white/40 underline-offset-2 hover:decoration-white'
        : 'text-indigo-700 underline decoration-indigo-300 underline-offset-2 hover:decoration-indigo-600';

    // Saved generation-form state (null for guests / never-saved users).
    $open = (bool) auth()->user()?->panel_advanced_open;

    // Get focus words from user
    $focusWords = request('focus_words', auth()->user()?->panel_focus_words ?? '');
@endphp

<details {{ $attributes->merge(['class' => 'w-full']) }} @if($open) open @endif 
    ontoggle="const i = this.querySelector('[name=advanced_panel_open]'); if (i) i.value = this.open ? 1 : 0;">

    <summary class="cursor-pointer select-none text-center text-[11px] font-semibold uppercase tracking-[0.2em] {{ $legendClass }}">
        Advanced &#9662;@if ($locked) <span class="ml-2 inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold normal-case tracking-normal align-middle {{ $badgeClass }}">🔒 Premium</span> @endif
    </summary>

    <!-- used to track if the advanced options panel is open  and connected to alpine via parent <details> tag-->
    <input type="hidden" name="advanced_panel_open" value="{{ $open ? 1 : 0 }}">

    <div class="mt-2 rounded-xl border-2 px-4 py-3 text-left md:px-12 {{ $boxClass }}">
        @if ($locked)
            <div class="mb-3 flex flex-wrap items-center gap-x-2 gap-y-1 rounded-lg px-3 py-2 text-xs ring-1 {{ $noteClass }}">
                <span class="font-semibold">🔒 Themes and Focus Words are Premium.</span>
                <a href="{{ route('premium') }}" class="font-semibold {{ $noteLinkClass }}">升级 · Upgrade to unlock →</a>
            </div>
        @endif

        <x-theme-chips :theme="$theme" :locked="$locked" />

        <div class="my-3 h-px {{ $ruleClass }}"></div>

        <label for="focus_words" class="block text-xs font-semibold uppercase tracking-[0.15em]">
            Focus Words &middot; 词
        </label>
        <p class="mt-1 text-xs {{ $helpClass }}">
            Enter up to 5 words (comma separated) and some of those words will show up in your text.
        </p>

        @if ($locked)
            <div class="group relative mt-2">
                <textarea rows="2" disabled placeholder="学习, 朋友, 天气"
                    class="pointer-events-none w-full cursor-not-allowed resize-none rounded-lg py-4 text-sm shadow-sm {{ $inputLocked }}"></textarea>
                {{-- Same -top-9 offset as every other tooltip: -top-4 parks it on the help line above during hover. --}}
                <span class="pointer-events-none absolute -top-9 left-1/2 z-20 -translate-x-1/2 whitespace-nowrap rounded-lg bg-[#1f2430] px-2.5 py-1.5 text-[10px] font-medium text-white opacity-0 shadow-xl transition group-hover:opacity-100">
                    Available with Premium ✨
                    <span class="absolute left-1/2 top-full -translate-x-1/2 border-4 border-transparent border-t-[#1f2430]"></span>
                </span>
            </div>
        @else
            <textarea name="focus_words" id="focus_words" rows="2"
                placeholder="学习, 朋友, 天气"
                class="mt-2 w-full resize-y rounded-lg py-4 text-sm shadow-sm {{ $inputClass }}">{{ $focusWords }}</textarea>
        @endif
    </div>
</details>
