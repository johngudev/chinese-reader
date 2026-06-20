<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">编辑 · Edit Article</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-4 flex items-center justify-between">
                <a href="{{ route('articles.show', $article) }}" class="text-sm text-gray-500 hover:text-seal">← View article</a>
            </div>
            <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-gray-100">
                @include('articles._form', [
                    'action'      => route('articles.update', $article),
                    'method'      => 'PUT',
                    'submitLabel' => 'Save changes',
                ])
            </div>
        </div>
    </div>
</x-app-layout>
