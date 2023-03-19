<div>
    {{-- Salary information --}}
    <header class="mb-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Salary Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Provide your salary's information and savings percentages.") }}
        </p>
    </header>

    <hr class="h-px my-6 w-80 bg-gray-200 border-0 dark:bg-gray-700">

    <div class="grid grid-cols-2 gap-4 mb-10">
        <div>
            <x-input-label for="month" :value="__('Month')" />
            <x-expenses-form.select-month-year-input id="month" name="month" class="block mt-1 w-full" :type="'month'" :values="$months" required autofocus />
            <x-input-error :messages="$errors->get('month')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="year" :value="__('Year')" />
            <x-expenses-form.select-month-year-input id="month" name="year" class="block mt-1 w-full" :type="'year'" :values="$years" required autofocus />
            <x-input-error :messages="$errors->get('year')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="salary" :value="__('Salary (RM)')" />
            <x-text-input id="salary" class="block mt-1 w-full" type="number" name="salary" min="0.00" step="any" :value="old('salary')" placeholder="0.00" required autofocus />
            <x-input-error :messages="$errors->get('salary')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="saving" :value="__('Saving (%)')" />
            <x-text-input id="saving" class="block mt-1 w-full" type="number" name="saving" min="20" step="5" :value="old('saving')" value="20" required autofocus />
            <x-input-error :messages="$errors->get('saving')" class="mt-2" />
        </div>
    </div>

    {{-- Commitment expenses information --}}
    <header class="mb-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Commitment Expenses Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Provide your commitment expenses information with appropriate amount.") }}
        </p>
    </header>

    <hr class="h-px my-6 w-80 bg-gray-200 border-0 dark:bg-gray-700">

    <div class="grid grid-cols-6 gap-4 mb-6 items-end">
        @forelse ($commitments_items as $commitments_item)   
            <div class="col-span-4">
                <x-input-label for="expenses-{{ $commitments_item }}" :value="__('Item')" />
                <x-text-input id="expenses-{{ $commitments_item }}" class="block mt-1 w-full" type="text" name="expenses.{{ $commitments_item }}" :value="old('expenses-'.$commitments_item)" placeholder="e.g. Loans, Insurances" required autofocus />
                <x-input-error :messages="$errors->get('expenses-'.$commitments_item)" class="mt-2" />
            </div>
            <div>
                <x-input-label for="amount-{{ $commitments_item }}" :value="__('Amount (RM)')" />
                <x-text-input id="amount-{{ $commitments_item }}" class="block mt-1 w-full" type="number" name="amount.{{ $commitments_item }}" min="0.00" step="any" :value="old('amount-'.$commitments_item)" placeholder="0.00" required autofocus />
                <x-input-error :messages="$errors->get('amount-'.$commitments_item)" class="mt-2" />
            </div>
            @if ($commitments_item != 0)
                <div>
                    <x-expenses-form.item-remove-button wire:click="removeCommitmentItem({{ $commitments_item }})">
                        {{ __('Remove Item') }}
                    </x-xpenses-form.item-remove-button>
                </div>
            @endif
        @empty
            <div class="col-span-5 text-red-700 dark:text-red-500">
                {{ __('Error occured while fetching expenses items field') }}
            </div>
        @endforelse
    </div>
    <div class="mb-10">
        <x-expenses-form.item-add-button wire:click="addCommitmentItem({{ $commitments_items }})">
            {{ __('Add Item') }}
        </x-xpenses-form.item-add-button>
    </div>

    {{-- Other expenses information --}}
    <header class="mb-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Other Expenses Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Provide your expenses information with appropriate amount.") }}
        </p>
    </header>
    
    <hr class="h-px my-6 w-80 bg-gray-200 border-0 dark:bg-gray-700">
    
    <div class="grid grid-cols-6 gap-4 mb-6 items-end">
        @forelse ($others_items as $others_item)   
            <div class="col-span-4">
                <x-input-label for="expenses-{{ $others_item }}" :value="__('Item')" />
                <x-text-input id="expenses-{{ $others_item }}" class="block mt-1 w-full" type="text" name="expenses.{{ $others_item }}" :value="old('expenses-'.$others_item)" placeholder="e.g. Utilities, Groceries" required autofocus />
                <x-input-error :messages="$errors->get('expenses-'.$others_item)" class="mt-2" />
            </div>
            <div>
                <x-input-label for="amount-{{ $others_item }}" :value="__('Amount (RM)')" />
                <x-text-input id="amount-{{ $others_item }}" class="block mt-1 w-full" type="number" name="amount.{{ $others_item }}" min="0.00" step="any" :value="old('amount-'.$others_item)" placeholder="0.00" required autofocus />
                <x-input-error :messages="$errors->get('amount-'.$others_item)" class="mt-2" />
            </div>
            @if ($others_item != 0)
                <div>
                    <x-expenses-form.item-remove-button wire:click="removeOtherItem({{ $others_item }})">
                        {{ __('Remove Item') }}
                    </x-xpenses-form.item-remove-button>
                </div>
            @endif
        @empty
            <div class="col-span-5 text-red-700 dark:text-red-500">
                {{ __('Error occured while fetching expenses items field') }}
            </div>
        @endforelse
    </div>
    <div class="mb-10">
        <x-expenses-form.item-add-button wire:click="addOtherItem({{ $others_items }})">
            {{ __('Add Item') }}
        </x-xpenses-form.item-add-button>
    </div>

    {{-- Planning summary --}}
    <header class="mb-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Plannig Summary') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Summaries of your expenses planning.") }}
        </p>
    </header>

    <hr class="h-px my-6 w-80 bg-gray-200 border-0 dark:bg-gray-700">

    {{-- Button submit --}}
    <div class="mb-6 float-right">
        <x-primary-button>
            {{ __('Submit') }}
        </x-primary-button>
    </div>
</div>
