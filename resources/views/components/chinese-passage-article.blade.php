@props(['story'])

{{-- Reading area --}}
<article class="pinyin-off px-8 py-10 sm:px-12 sm:py-14 bg-white relative">

    {{-- Pinyin toggle (top right) --}}
    <label class="absolute top-8 right-4 sm:top-8 sm:right-8 inline-flex cursor-pointer select-none items-center gap-3">
        <span class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500">Pinyin</span>
        <span class="relative inline-block h-6 w-11">
            <input type="checkbox" id="pinyin-toggle" class="peer sr-only">
            <span class="absolute inset-0 rounded-full bg-gray-300 transition-colors duration-200 peer-checked:bg-seal"></span>
            <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform duration-200 peer-checked:translate-x-5"></span>
        </span>
    </label>

    <p id="generated-chinese-passage" class="whitespace-pre-line text-3xl leading-loose tracking-wide text-gray-900"
        style="font-family: 'Noto Serif SC', 'Songti SC', serif;">
        {{ $story }}
    </p>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const checkbox = document.getElementById('pinyin-toggle');
        const article  = checkbox.closest('article');

        checkbox.addEventListener('change', () => {
            article.classList.toggle('pinyin-off', !checkbox.checked);
        });
    });
    </script>
</article>

<script>

document.addEventListener('DOMContentLoaded', function () {
    let wrapped = wrapTextInPinyin(document.getElementById("generated-chinese-passage").textContent);
    console.log(wrapped);

    document.getElementById("generated-chinese-passage").innerHTML = wrapped;
            });

</script>

<style>
.hanzi {
    position: relative;
}

.pinyin-off .hanzi::before {
    display: none;
}

.hanzi::before {
    content: attr(data-pinyin);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    font-size: 0.5em;
    color: #6b6358;
    font-family: 'Inter', -apple-system, sans-serif;
    line-height: 1;
    white-space: nowrap;
    user-select: none;
    -webkit-user-select: none;
    pointer-events: none;
}
</style>