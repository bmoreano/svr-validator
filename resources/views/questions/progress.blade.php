<div>
    <x-app-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Validando Reactivo: {{ $question->code }}
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                {{-- Aquí llamamos al componente Livewire que creamos --}}
                @livewire('validation-progress', ['question' => $question])
            </div>
        </div>
    </x-app-layout>
</div>
