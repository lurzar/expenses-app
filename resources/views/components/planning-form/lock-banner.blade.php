@props(['allowed_date', 'current_month_year'])

<div>
    {{-- Form locked --}}
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            Form Locked !
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Please wait until {{ $allowed_date }} {{ $current_month_year }}.
        </p>
    </header>
</div>