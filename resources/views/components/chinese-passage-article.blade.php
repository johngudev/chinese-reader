@props(['story','definitions','chinese','english','savedWords' => [], 'textId' => null])

@if(!empty($definitions))
<script>
    const definitions = @json($definitions);
</script>
@endif

{{-- Reading area --}}
<article class="pinyin-off px-8 py-10 sm:px-12 sm:py-14 bg-white relative">

    @if(auth()->check() && auth()->user()->isPremium())
    {{-- Pinyin toggle + Print (top right) --}}
    <div class="absolute top-8 right-4 sm:right-8 flex items-center gap-4">
        <label class="inline-flex cursor-pointer select-none items-center gap-3">
            <span class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500">Pinyin</span>
            <span class="relative inline-block h-6 w-11">
                <input type="checkbox" id="pinyin-toggle" class="peer sr-only">
                <span class="absolute inset-0 rounded-full bg-gray-300 transition-colors duration-200 peer-checked:bg-seal"></span>
                <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform duration-200 peer-checked:translate-x-5"></span>
            </span>
        </label>

        {{-- Print / Download --}}
        <button type="button" onclick="window.print()" aria-label="Print or download"
            class="no-print inline-flex h-8 w-8 items-center justify-center rounded-full text-gray-500 transition hover:bg-gray-100 hover:text-seal">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                stroke-width="1.6" stroke="currentColor" class="h-5 w-5">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.4 42.4 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48 48 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48 48 0 0 1 1.913-.247m10.5 0a48.5 48.5 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Z" />
            </svg>
        </button>

        {{-- Styling --}}
        <style media="print">
            @page { margin: 0.2cm 0.5cm; }

            /* hide chrome + interactive bits */
            nav, header, .no-print,
            #pinyin-toggle, label:has(#pinyin-toggle),   /* pinyin switch */
            #word-tooltip { display: none !important; }

            /* the passage itself */
            article { box-shadow: none !important; }
            #generated-chinese-passage {
                font-family: 'Noto Serif SC', serif;
                font-size: 18px;
                line-height: 2.1;
            }
        </style>
    </div>
    @else
    {{-- Pinyin toggle (top right) --}}
    <label class="absolute top-8 right-4 sm:top-8 sm:right-8 inline-flex cursor-pointer select-none items-center gap-3">
        <span class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-500">Pinyin</span>
        <span class="relative inline-block h-6 w-11">
            <input type="checkbox" id="pinyin-toggle" class="peer sr-only">
            <span class="absolute inset-0 rounded-full bg-gray-300 transition-colors duration-200 peer-checked:bg-seal"></span>
            <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform duration-200 peer-checked:translate-x-5"></span>
        </span>
    </label>
    @endif

    <p id="generated-chinese-passage" class="whitespace-pre-line text-3xl leading-loose tracking-wide text-gray-900 mt-4 mb-8"
        style="font-family: 'Noto Serif SC', 'Songti SC', serif;">
        {{ $chinese }}
    </p>
        <hr>
    <p class="whitespace-pre-line text-2xl leading-loose tracking-wide text-gray-900"
    style="font-family: 'Noto Serif SC', 'Songti SC', serif;">
    {{ $english }}
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

<style>
    /* whole-word hover highlight + pointer */
    #generated-chinese-passage .word {
        display: inline-block;              /* forces word to not do line break */
        position: relative;                 /* ← anchors the pinyin above this word */
        border-radius: 4px;
        padding: 0 1px;
        transition: background-color 0.12s ease;
    }
    #generated-chinese-passage .word:hover {
        background-color: rgba(192, 57, 43, 0.12);
        cursor: pointer;
    }

    /* tooltip */
    #word-tooltip {
        position: absolute;
        z-index: 60;
        max-width: 280px;
        display: none;
        background: #1f2430;
        color: #fff;
        padding: 10px 13px;
        border-radius: 12px;
        box-shadow: 0 8px 28px rgba(0, 0, 0, 0.22);
        font-family: 'Inter', -apple-system, sans-serif;
        font-size: 14px;
        line-height: 1.45;
    }
    #word-tooltip .tip-word {
        font-family: 'Noto Serif SC', serif;
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 2px;
    }
    #word-tooltip .tip-pinyin  { color: #f3a39b; }
    #word-tooltip .tip-english { color: #e5e7eb; margin-bottom: 8px; }
    #word-tooltip .tip-english:last-child { margin-bottom: 0; }

    /* word-level pinyin, shown/hidden by the .pinyin-off class on <article> */
    #generated-chinese-passage .word::before {
        content: attr(data-pinyin);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translate(-50%, 0.75rem);
        font-size: 0.4375em;
        color: #6b6358;
        font-family: 'Inter', -apple-system, sans-serif;
        line-height: 1;
        white-space: nowrap;
        user-select: none;
        -webkit-user-select: none;
        pointer-events: none;
    }
    .pinyin-off #generated-chinese-passage .word::before {
        display: none;
    }

</style>

@if(!empty($definitions))
<script>
document.addEventListener('DOMContentLoaded', () => {
    const passage = document.getElementById('generated-chinese-passage');

    // 1) Render the passage as word spans from `definitions`
    passage.innerHTML = '';
    let lastSpan = null;
    definitions.forEach((token, i) => {
        if (token.entries && token.entries.length) {
            const span = document.createElement('span');
            span.className = 'word';
            span.dataset.i = i;
            span.dataset.pinyin = token.entries[0].pinyin;
            span.textContent = token.word;
            passage.appendChild(span);
            lastSpan = span;
        } else {
            // punctuation / whitespace / unmatched — may contain newlines.
            // keep the \n delimiters as their own array elements:
            token.word.split(/(\r\n|\r|\n)/).forEach(part => {
                if (part === '') return;
                if (/[\r\n]/.test(part)) {
                    passage.appendChild(document.createElement('br')); // real line break
                    lastSpan = null;                                   // break the glue chain
                } else if (lastSpan) {
                    lastSpan.textContent += part;                      // glue punctuation
                } else {
                    passage.appendChild(document.createTextNode(part));
                }
            });
        }
    });


    // 2) One shared tooltip element
    const tip = document.createElement('div');
    tip.id = 'word-tooltip';
    document.body.appendChild(tip);

    function showWord(span) {
        const token = definitions[+span.dataset.i];
        tip.innerHTML = '';

        const head = document.createElement('div');
        head.className = 'tip-word';
        head.textContent = token.word;
        tip.appendChild(head);

        // a word can have several readings (homographs) — show them all
        token.entries.forEach(entry => {
            const py = document.createElement('div');
            py.className = 'tip-pinyin';
            py.textContent = entry.pinyin;
            tip.appendChild(py);

            const en = document.createElement('div');
            en.className = 'tip-english';
            en.textContent = entry.english.replace(/\//g, ' · '); // slash-separated → readable
            tip.appendChild(en);
        });

        const r = span.getBoundingClientRect();
        tip.style.display = 'block';
        tip.style.top  = (window.scrollY + r.bottom + 8) + 'px';
        tip.style.left = (window.scrollX + r.left) + 'px';
    }

    async function saveWord(i) {
        const token = definitions[i];
        if (!token.entries?.length) return;   // skip punctuation / unknown chars
        const entry = token.entries[0];
        try {
            const res = await fetch('/saved-words', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: JSON.stringify({
                    generated_text_id: SAVE_TEXT_ID,
                    word: token.word, pinyin: entry.pinyin, english: entry.english,
                }),
            });
            if (res.ok) window.dispatchEvent(new CustomEvent('word-saved', { detail: await res.json() }));
        } catch {}
    }


    const SAVE_TEXT_ID = {{ $textId ?? 'null' }};
    const CAN_SAVE     = {{ (auth()->check() && $textId) ? 'true' : 'false' }};


    // 3) Click a word → show; click elsewhere → hide
    passage.addEventListener('click', e => {
        const span = e.target.closest('.word');
        if (span) {
            showWord(span);
            if (CAN_SAVE) saveWord(+span.dataset.i);
        }
    });
    document.addEventListener('click', e => {
        if (!e.target.closest('.word') && !e.target.closest('#word-tooltip')) {
            tip.style.display = 'none';
        }
    });
});
</script>
@endif