<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            <i class="fa-solid fa-clipboard-list"></i>
            &nbsp;
            @lang('common.planning')
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if ($form_is_unlock)
                        <section id="planningForm">
                            @livewire('planning', ['close_date' => $form_close_date])
                        </section>
                    @else
                        <section id="planningLock">
                            <x-planning-form.lock-banner :open_date="$form_open_date"/>
                        </section>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
