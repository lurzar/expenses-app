<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            <i class="fa-solid fa-clipboard-list"></i>
            &nbsp;
            @lang('common.planning')
        </h2>
    </x-slot>

    <section id="planningCreateSection">
        @if ($form_is_unlock)
            @livewire('planning', ['close_date' => $form_close_date])
        @else
            <div class="hero">
                <div class="hero-content text-center">
                    <div class="max-w-md">
                        <h1 class="text-5xl font-bold">
                            Form Locked
                        </h1>
                        <p class="py-6">
                            Please wait until {{ $form_open_date ?? '' }}.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </section>
</x-app-layout>
