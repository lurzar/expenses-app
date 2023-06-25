@props(['open_date'])

<div>
    {{-- Form locked --}}
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            <svg 
                xmlns="http://www.w3.org/2000/svg" 
                fill="none" 
                viewBox="0 0 24 24" 
                stroke-width="1.5" 
                stroke="currentColor" 
                class="w-6 h-6"
            >
                <path 
                    stroke-linecap="round" 
                    stroke-linejoin="round" 
                    d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" 
                />
            </svg>
            Form Locked
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Please wait until {{ $open_date }}.
        </p>
    </header>
</div>