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
            <div class="card bg-base-100">
                <div class="card-body">
                    <form action="{{ route('planning.store') }}" method="POST">
                        @csrf
                        @livewire('planning', ['close_date' => $form_close_date])
                        <div class="mb-6 float-right">
                            <x-primary-button type="submit" :icon="'submit'">
                                @lang('common.submit')
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <div class="hero">
                <div class="hero-content text-center">
                    <div class="max-w-md">
                        <h1 class="text-5xl font-bold flex">
                            <svg 
                                xmlns="http://www.w3.org/2000/svg" 
                                fill="none" 
                                viewBox="0 0 24 24" 
                                stroke-width="1.5" 
                                stroke="currentColor" 
                                class="stroke-current h-11 w-11"
                            >
                                <path 
                                    stroke-linecap="round" 
                                    stroke-linejoin="round" 
                                    d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" 
                                />
                            </svg>
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
