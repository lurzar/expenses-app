@props([
    'balance_after_commitments' => 0,
    'others_items' => [],
    'others_values' => []
])

<div>
    <x-planning-form.section-header 
        :title="__('Other Expenses Information')" 
        :description="__('Provide your expenses information with appropriate amount.')"
        :balance_after_commitments="$balance_after_commitments"
        :custom="true"
    />

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
</div>