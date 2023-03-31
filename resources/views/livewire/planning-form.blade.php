<div>
    <form action="{{ route('planning.store') }}" method="POST">
        @csrf
        <x-planning-form.section.salary 
            :current_month="$current_month" 
            :current_year="$current_year" 
            :salary="$salary" 
            :saving_rate="$saving_rate" 
        />

        <x-planning-form.section.saving 
            :total_savings="$total_savings" 
            :balance_after_total_savings="$balance_after_total_savings"
            :savings_items="$savings_items"
            :savings_values="$savings_values" 
        />

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
                            id="balance_after_total_savings" 
                            class="block mt-1 dark:text-gray-500 text-right" 
                            type="number" 
                            name="balance_after_total_savings" 
                            min="0" 
                            step="any" 
                            wire:model="balance_after_total_savings" 
                            :value="old('balance_after_total_savings')"  
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
                    <x-input-label for="commitments_amount_{{ $commitments_item }}" :value="__('Amount (RM)')" />
                    <x-text-input 
                        id="commitments_amount_{{ $commitments_item }}" 
                        class="block mt-1 w-full" 
                        type="number" 
                        name="commitments_amount.{{ $commitments_item }}"
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
                        <x-planning-form.item-remove-button type="button" wire:click="removeCommitmentItem({{ $commitments_item }})">
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
            <x-planning-form.item-add-button type="button" wire:click="addCommitmentItem({{ $commitments_items }})">
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
                        <x-planning-form.item-remove-button type="button" wire:click="removeOtherItem({{ $others_item }})">
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
            <x-planning-form.item-add-button type="button" wire:click="addOtherItem({{ $others_items }})">
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
            <x-primary-button type="submit">
                {{ __('Submit') }}
            </x-primary-button>
        </div>
    </form>
</div>