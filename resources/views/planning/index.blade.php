<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Planning') }}
        </h2>
    </x-slot>

    <div class="my-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if ($formIsUnlock)
                        @livewire('planning-form')
                    @else
                        <x-planning-form.lock-banner :allowed_date="$allowed_date" :current_month_year="$currentMonthYear" />
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>