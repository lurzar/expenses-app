@props([
    'title' => '', 
    'description' => '',
    'custom' => true,
    'res_total_saving' => 0, 
    'res_balance' => 0,
])

<header class="mb-6">
    @if (! $custom)
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ $title }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ $description }}
        </p>
    @else
        <div class="grid grid-cols-2 gap-4">
            <div>
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ $title }} {{ ($res_total_saving) == 0 ? '' : '(RM '.$res_total_saving.')'  }}
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ $description }}
                </p>
            </div>
            <div class="grid grid-cols-1 place-items-end">
                <div>
                    <x-text-input 
                        id="res_balance" 
                        class="block mt-1 dark:text-gray-500 text-right" 
                        type="number" 
                        name="res_balance" 
                        min="0" 
                        step="any"
                        :value="old('res_balance') ?? $res_balance"  
                        :disabled="true" 
                    />
                </div>
            </div>
        </div>
    @endif
</header>

<hr class="h-px my-6 w-80 bg-gray-200 border-0 dark:bg-gray-700">