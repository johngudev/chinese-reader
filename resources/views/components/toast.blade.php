{{--
    Global toast. Fire from anywhere:

        window.dispatchEvent(new CustomEvent('toast', { detail: { message: '…' } }))

    The message is supplied by the caller — no copy is baked in here.
    Included once in layouts/app.blade.php.
--}}

<style>[x-cloak] { display: none !important; }</style>

<div x-data="{
        show: false,
        message: '',
        timer: null,
        flash(message) {
            this.message = message;
            this.show = true;
            clearTimeout(this.timer);
            this.timer = setTimeout(() => this.show = false, 2200);
        },
     }"
     x-on:toast.window="flash($event.detail?.message ?? '')"
     x-show="show"
     x-cloak
     x-transition:enter="transition duration-200"
     x-transition:enter-start="translate-y-2 opacity-0"
     x-transition:enter-end="translate-y-0 opacity-100"
     x-transition:leave="transition duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="no-print fixed bottom-8 left-1/2 z-50 flex -translate-x-1/2 items-center gap-2 rounded-xl bg-[#1f2430] px-4 py-2.5 text-sm font-medium text-white shadow-2xl">

    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
         stroke-width="2.5" stroke="currentColor" class="h-4 w-4 flex-none text-green-400">
        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
    </svg>

    <span x-text="message"></span>
</div>
