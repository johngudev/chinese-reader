@props(['chinese', 'textId'])

<style>[x-cloak] { display: none !important; }</style>

{{--
    Share button — sits under everything on the story page.
    Copies the Chinese text + a link to this text; on touch devices it
    opens the native share sheet instead. Plug in with:

        <x-share-text :chinese="$chinese" :text-id="$textId" />
--}}

<div x-data="{
        copied: false,
        payload: @js(trim($chinese) . "\n→ " . url('/texts/' . $textId)),
        async share() {
            // Touch devices with a native share sheet get the real thing
            if (navigator.share && window.matchMedia('(pointer: coarse)').matches) {
                try { await navigator.share({ text: this.payload }); return; }
                catch (e) { if (e.name === 'AbortError') return; /* else fall through to copy */ }
            }
            // Desktop / fallback: clipboard + flash
            try {
                await navigator.clipboard.writeText(this.payload);
            } catch (e) {
                // http:// dev domains have no navigator.clipboard — legacy fallback
                const ta = document.createElement('textarea');
                ta.value = this.payload;
                ta.style.position = 'fixed'; ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                ta.remove();
            }
            this.copied = true;
            clearTimeout(this._t);
            this._t = setTimeout(() => this.copied = false, 2000);
        }
    }"
    class="no-print mx-auto flex max-w-3xl justify-center px-2 pt-6 sm:px-6 lg:px-8">

    <button type="button" @click="share()"
        class="inline-flex w-full items-center justify-center gap-2 rounded-xl border px-6 py-3.5 text-sm font-semibold shadow-sm transition sm:w-auto sm:min-w-[16rem]"
        :class="copied
            ? 'border-green-200 bg-green-50 text-green-700'
            : 'border-gray-200 bg-white text-gray-600 hover:-translate-y-px hover:border-red-200 hover:text-seal hover:shadow'">

        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
            stroke-width="1.8" stroke="currentColor" class="h-[1.125rem] w-[1.125rem] flex-none">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" />
        </svg>

        <span x-show="!copied">分享 · Share this text</span>
        <span x-show="copied" x-cloak>已复制 · Copied!</span>
    </button>

    {{-- toast flash --}}
    <div x-show="copied" x-cloak
        x-transition:enter="transition duration-200"
        x-transition:enter-start="translate-y-2 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed bottom-8 left-1/2 z-50 flex -translate-x-1/2 items-center gap-2 rounded-xl bg-[#1f2430] px-4 py-2.5 text-sm font-medium text-white shadow-2xl">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
            stroke-width="2.5" stroke="currentColor" class="h-4 w-4 text-green-400">
            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
        </svg>
        已复制 · Text and link copied to clipboard
    </div>
</div>
