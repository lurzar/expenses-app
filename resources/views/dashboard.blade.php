<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight">
            <i class="fa-solid fa-gauge-high"></i>
            &nbsp;
            @lang('common.dashboard')
        </h2>
    </x-slot>

    {{ __("You're on dashboard page!") }}
</x-app-layout>
