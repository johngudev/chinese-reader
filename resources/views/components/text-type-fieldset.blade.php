@props(['theme' => 'dark'])

@php
    $options = [
        '' => 'Surprise me',
        'story' => 'Story',
        'news' => 'News',
        'article' => 'Article',
    ];

    $legendClass = $theme === 'dark' ? 'text-white/80' : 'text-gray-500';

    $pillBase = 'flex items-center justify-center rounded-xl border-2 px-4 py-2.5 text-sm font-semibold shadow-sm transition';

    // NOTE: keep these as complete literal class strings so Tailwind's
    // content scanner can see them (don't build them from fragments).
    $pillTheme = $theme === 'dark'
        ? 'border-white/40 bg-white/10 text-white hover:bg-white/20 peer-checked:border-white peer-checked:bg-white peer-checked:text-indigo-700 peer-checked:shadow-lg peer-focus-visible:ring-2 peer-focus-visible:ring-white'
        : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50 peer-checked:border-indigo-600 peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:shadow-lg peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-500';
@endphp

<fieldset {{ $attributes->merge(['class' => 'w-72 max-w-full']) }}>
    <legend class="mb-2 block w-full text-center text-[11px] font-semibold uppercase tracking-[0.2em] {{ $legendClass }}">
        Choose your text type
    </legend>
    <div class="grid grid-cols-2 gap-2">
        @foreach ($options as $value => $label)
            <label class="cursor-pointer">
                <input type="radio" name="variety" value="{{ $value }}" class="peer sr-only" @checked($value === '')>
                <span class="{{ $pillBase }} {{ $pillTheme }}">
                    {{ $label }}
                </span>
            </label>
        @endforeach
    </div>
</fieldset>