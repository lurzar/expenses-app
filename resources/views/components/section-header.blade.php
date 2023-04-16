@props([
    'title' => '', 
    'description' => '',
    'show_saving' => false,
    'show_balance' => true,
    'res_total_saving' => 0, 
    'res_balance' => 0,
])

<header class="mb-6">
    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
        {{ $slot }} 
        {{ $title }}
        @if ($show_balance)
            <span class="text-sm italic text-slate-500">
                @if ($show_saving)
                    @if ($res_total_saving != 0)
                        (@lang('common.saving'): RM {{ $res_total_saving }}, @lang('common.balance'): RM {{ $res_balance }})
                    @endif
                @else
                    @if ($res_balance != 0)
                        (@lang('common.balance'): RM {{ $res_balance }})
                    @endif
                @endif
            </span>
        @endif
        </span>
    </h2>
    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        {{ $description }}
    </p>
</header>

<hr class="h-px my-6 w-80 bg-gray-200 border-0 dark:bg-gray-700">