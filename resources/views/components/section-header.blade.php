@props([
    'title' => '', 
    'description' => '',
    'show_total_balance' => true,
    'res_total' => 0, 
    'res_balance' => 0,
])

<header class="mb-6">
    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
        {{ $slot }} 
        {{ $title }}
        @if ($show_total_balance)
            <span class="text-sm italic text-slate-500">
                (@lang('common.total'): RM {{ $res_total }}, @lang('common.balance'): RM {{ $res_balance }})
            </span>
        @endif
        </span>
    </h2>
    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        {{ $description }}
    </p>
</header>

<hr class="h-px my-6 w-80 bg-gray-200 border-0 dark:bg-gray-700">