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
        {{ $title }}
        <span class="text-sm italic text-slate-500">
            @if ($show_balance)
                @if ($show_saving)
                    {{ ($res_total_saving) == 0 ? '' : '(Saving: RM '.$res_total_saving.', Balance: RM '.$res_balance.')'  }}
                @else
                    {{ ($res_balance) == 0 ? '' : '(Balance: RM '.$res_balance.')'  }}
                @endif
            @endif
        </span>
    </h2>
    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        {{ $description }}
    </p>
</header>

<hr class="h-px my-6 w-80 bg-gray-200 border-0 dark:bg-gray-700">