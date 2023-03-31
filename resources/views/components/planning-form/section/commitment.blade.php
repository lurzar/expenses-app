@props([
    'balance_after_savings' => 0,
    'commitments_items' => [],
    'commitments_values' => []
])

<div>
    <x-planning-form.section-header 
        :title="__('Commitment Expenses Information')" 
        :description="__('Provide your commitment expenses information with appropriate amount.')"
        :balance_after_savings="$balance_after_savings"
        :custom="true"
    />

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
</div>