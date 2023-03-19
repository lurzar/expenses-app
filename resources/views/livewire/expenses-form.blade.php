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

    {{-- Expenses items --}}
    <div class="grid grid-cols-6 gap-4 mb-6 items-end">
        @forelse ($expenses_items as $expenses_item)   
            <div class="col-span-4">
                <x-input-label for="expenses-{{ $expenses_item }}" :value="__('Expenses Items')" />
                <x-text-input id="expenses-{{ $expenses_item }}" class="block mt-1 w-full" type="text" name="expenses.{{ $expenses_item }}" :value="old('expenses-'.$expenses_item)" placeholder="e.g. Utilities, Groceries" required autofocus />
                <x-input-error :messages="$errors->get('expenses-'.$expenses_item)" class="mt-2" />
            </div>
            <div>
                <x-input-label for="amount-{{ $expenses_item }}" :value="__('Amount (RM)')" />
                <x-text-input id="amount-{{ $expenses_item }}" class="block mt-1 w-full" type="number" name="amount.{{ $expenses_item }}" min="0.00" step="any" :value="old('amount-'.$expenses_item)" placeholder="0.00" required autofocus />
                <x-input-error :messages="$errors->get('amount-'.$expenses_item)" class="mt-2" />
            </div>
            @if ($expenses_item != 0)
                {{-- Button remove item --}}
                <div>
                    <x-expenses-form.item-remove-button wire:click="removeExpensesItem({{ $expenses_item }})">
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
    {{-- Button add item --}}
    <div class="mb-6">
        <x-expenses-form.item-add-button wire:click="addExpensesItem({{ $expenses_items }})">
            {{ __('Add Item') }}
        </x-xpenses-form.item-add-button>
    </div>
    {{-- Button submit --}}
    <div class="mb-6 float-right">
        <x-primary-button>
            {{ __('Submit') }}
        </x-primary-button>
    </div>
</div>
