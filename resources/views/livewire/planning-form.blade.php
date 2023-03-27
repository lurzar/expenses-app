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
            <x-input-label for="current_month" :value="__('Month')" />
            <x-text-input id="current_month" class="block mt-1 w-full dark:text-gray-500" type="text" name="current_month" :value="$current_month" :disabled="true" required autofocus />
        </div>
        <div>
            <x-input-label for="current_year" :value="__('Year')" />
            <x-text-input id="current_year" class="block mt-1 w-full dark:text-gray-500" type="text" name="current_year" :value="$current_year" :disabled="true" required autofocus  />
        </div>
        <div>
            <x-input-label for="salary" :value="__('Salary (RM)')" />
            <x-text-input 
                id="salary" 
                class="block mt-1 w-full" 
                type="number" 
                name="salary" 
                min="0.00" 
                step="any" 
                wire:model="salary"
                :value="old('salary')" 
                placeholder="0.00" 
                required 
                autofocus 
            />
            <x-input-error :messages="$errors->get('salary')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="saving" :value="__('Saving (%)')" />
            <x-text-input 
                id="saving" 
                class="block mt-1 w-full" 
                type="number" 
                name="saving" 
                min="20" 
                step="5" 
                wire:model="saving_rate" 
                :value="old('saving_rate')"  
                required 
                autofocus 
            />
            <x-input-error :messages="$errors->get('saving_rate')" class="mt-2" />
        </div>
    </div>

    {{-- Commitment expenses information --}}
    <header class="mb-6">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ __('Commitment Expenses Information') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __("Provide your commitment expenses information with appropriate amount.") }}
                </p>
            </div>
            <div class="grid grid-cols-1 place-items-end">
                <div>
                    <x-text-input 
                        id="balance_after_saving_rate" 
                        class="block mt-1 dark:text-gray-500 text-right" 
                        type="number" 
                        name="balance_after_saving_rate" 
                        min="0" 
                        step="any" 
                        wire:model="balance_after_saving_rate" 
                        :value="old('balance_after_saving_rate')"  
                        :disabled="true" 
                    />
                </div>
            </div>
        </div>
    </header>

    <hr class="h-px my-6 w-80 bg-gray-200 border-0 dark:bg-gray-700">

    <div class="grid grid-cols-6 gap-4 mb-6 items-end">
        @forelse ($commitments_items as $commitments_item)   
            <div class="col-span-4">
                <x-input-label for="commitments-{{ $commitments_item }}" :value="__('Item')" />
                <x-text-input 
                    id="commitments-{{ $commitments_item }}" 
                    class="block mt-1 w-full" 
                    type="text" 
                    name="commitments.{{ $commitments_item }}" 
                    wire:model="commitments_values.{{ $commitments_item }}.item" 
                    :value="old('commitments_values.'.$commitments_item.'.item')" 
                    placeholder="e.g. Loans, Insurances" 
                    required 
                    autofocus 
                />
                <x-input-error :messages="$errors->get('commitments_values.'.$commitments_item.'.item')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="commitments-amount-{{ $commitments_item }}" :value="__('Amount (RM)')" />
                <x-text-input 
                    id="commitments-amount-{{ $commitments_item }}" 
                    class="block mt-1 w-full" 
                    type="number" 
                    name="commitments-amount.{{ $commitments_item }}"
                    wire:model="commitments_values.{{ $commitments_item }}.amount"  
                    min="0.00" 
                    step="any" 
                    :value="old('commitments_values.'.$commitments_item.'.amount')" 
                    placeholder="0.00" 
                    required 
                    autofocus 
                />
                <x-input-error :messages="$errors->get('commitments_values.'.$commitments_item.'.amount')" class="mt-2" />
            </div>
            @if ($commitments_item != 0)
                <div>
                    <x-planning-form.item-remove-button wire:click="removeCommitmentItem({{ $commitments_item }})">
                        {{ __('Remove Item') }}
                    </x-planning-form.item-remove-button>
                </div>
            @endif
        @empty
            <div class="col-span-5 text-red-700 dark:text-red-500">
                {{ __('Error occured while fetching expenses items field') }}
            </div>
        @endforelse
    </div>
    <div class="mb-10">
        <x-planning-form.item-add-button wire:click="addCommitmentItem({{ $commitments_items }})">
            {{ __('Add Item') }}
        </x-planning-form.item-add-button>
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
                <x-input-label for="others-{{ $others_item }}" :value="__('Item')" />
                <x-text-input 
                    id="others-{{ $others_item }}" 
                    class="block mt-1 w-full" 
                    type="text" 
                    name="others.{{ $others_item }}" 
                    wire:model="others_values.{{ $others_item }}.item" 
                    :value="old('others_values.'.$others_item.'.item')" 
                    placeholder="e.g. Utilities, Groceries" 
                    required 
                    autofocus 
                />
                <x-input-error :messages="$errors->get('others_values.'.$others_item.'.item')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="others-amount-{{ $others_item }}" :value="__('Amount (RM)')" />
                <x-text-input 
                    id="others-amount-{{ $others_item }}" 
                    class="block mt-1 w-full" 
                    type="number" 
                    name="others-amount.{{ $others_item }}" 
                    wire:model="others_values.{{ $others_item }}.amount" 
                    min="0.00" 
                    step="any" 
                    :value="old('others_values.'.$others_item.'.amount')" 
                    placeholder="0.00" 
                    required 
                    autofocus 
                />
                <x-input-error :messages="$errors->get('others_values.'.$others_item.'.amount')" class="mt-2" />
            </div>
            @if ($others_item != 0)
                <div>
                    <x-planning-form.item-remove-button wire:click="removeOtherItem({{ $others_item }})">
                        {{ __('Remove Item') }}
                    </x-planning-form.item-remove-button>
                </div>
            @endif
        @empty
            <div class="col-span-5 text-red-700 dark:text-red-500">
                {{ __('Error occured while fetching expenses items field') }}
            </div>
        @endforelse
    </div>
    <div class="mb-10">
        <x-planning-form.item-add-button wire:click="addOtherItem({{ $others_items }})">
            {{ __('Add Item') }}
        </x-planning-form.item-add-button>
    </div>

    {{-- Planning summary --}}
    <header class="mb-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Planning Summary') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Summaries of your expenses planning.") }}
        </p>
    </header>

    <hr class="h-px my-6 w-80 bg-gray-200 border-0 dark:bg-gray-700">

    <div class="grid grid-cols-2 gap-4 mb-10">
        <div>
            <x-input-label for="total-saving" :value="__('Total Saving (RM)')" />
            <x-text-input id="total-saving" class="block mt-1 w-full" type="text" name="total-saving" :value="old('total-saving')" :disabled="true" />
            <x-input-error :messages="$errors->get('total-saving')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="total-change" :value="__('Total Change (RM)')" />
            <x-text-input id="total-change" class="block mt-1 w-full" type="text" name="total-change" :value="old('total-change')" :disabled="true" />
            <x-input-error :messages="$errors->get('total-change')" class="mt-2" />
        </div>
    </div>

    {{-- Button submit --}}
    <div class="mb-6 float-right">
        <x-primary-button>
            {{ __('Submit') }}
        </x-primary-button>
    </div>
</div>