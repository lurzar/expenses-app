@props([
    'title' => '', 
    'description' => '', 
    'total_saving' => 0, 
    'balance_after_saving_rates' => 0, 
    'custom' => false
])

<div>
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
                        {{ __('Savings Information') }} {{ ($total_saving) == 0 ? '' : '(RM '.$total_saving.')'  }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ __("Provide your savings information with appropriate amount.") }}
                    </p>
                </div>
                <div class="grid grid-cols-1 place-items-end">
                    <div>
                        <x-text-input 
                            id="balance_after_saving_rates" 
                            class="block mt-1 dark:text-gray-500 text-right" 
                            type="number" 
                            name="balance_after_saving_rates" 
                            min="0" 
                            step="any" 
                            wire:model="balance_after_saving_rates" 
                            :value="old('balance_after_saving_rates')"  
                            :disabled="true" 
                        />
                    </div>
                </div>
            </div>
        @endif
    </header>

    <hr class="h-px my-6 w-80 bg-gray-200 border-0 dark:bg-gray-700">
</div>