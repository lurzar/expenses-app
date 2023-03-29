@props(['title' => '', 'description' => '', 'total_saving' => 0, 'balance' => 0])

<div>
    <header class="mb-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ $title }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ $description }}
        </p>
    </header>

    <hr class="h-px my-6 w-80 bg-gray-200 border-0 dark:bg-gray-700">
</div>