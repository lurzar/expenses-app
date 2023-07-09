@props([
    'title' => '', 
    'description' => '',
    'show_total_balance' => false,
    'res_total' => 0, 
    'res_balance' => 0,
])

<header>
    <h2 class="text-lg font-medium">
        {{ $slot }} 
        {{ $title }}
        @if ($show_total_balance)
            <span class="text-sm italic">
                (@lang('common.total'): RM {{ $res_total }}, @lang('common.balance'): RM {{ $res_balance }})
            </span>
        @endif
        </span>
    </h2>
    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        {{ $description }}
    </p>
</header>

<div class="divider w-80"></div> 