@props(['res_savings_items' => []])

<div class="grid grid-cols-6 gap-4 mb-6 items-end">
    @forelse ($res_savings_items as $savings_item)   
        <div class="col-span-4">
            <x-input-label for="savings_{{ $savings_item }}" :value="__('Item')" />
            <x-text-input 
                id="savings_{{ $savings_item }}" 
                class="block mt-1 w-full" 
                type="text" 
                name="savings.{{ $savings_item }}" 
                wire:model="savings_values.{{ $savings_item }}.item" 
                :value="old('savings_values.'.$savings_item.'.item')" 
                placeholder="e.g. Loans, Insurances" 
                required 
                autofocus 
            />
            <x-input-error :messages="$errors->get('savings_values.'.$savings_item.'.item')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="savings_amount_{{ $savings_item }}" :value="__('Amount (RM)')" />
            <x-text-input 
                id="savings_amount_{{ $savings_item }}" 
                class="block mt-1 w-full" 
                type="number" 
                name="savings_amount.{{ $savings_item }}"
                wire:model="savings_values.{{ $savings_item }}.amount"  
                min="0.00" 
                step="any" 
                :value="old('savings_values.'.$savings_item.'.amount')" 
                placeholder="0.00" 
                required 
                autofocus 
            />
            <x-input-error :messages="$errors->get('savings_values.'.$savings_item.'.amount')" class="mt-2" />
        </div>
        @if ($savings_item != 0)
            <div>
                <x-planning-form.item-remove-button type="button" wire:click="removeSavingItem({{ $savings_item }})">
                    {{ __('Remove Item') }}
                </x-planning-form.item-remove-button>
            </div>
        @endif
    @empty
        <div class="col-span-5 text-red-700 dark:text-red-500">
            {{ __('Error occured while fetching savings items field') }}
        </div>
    @endforelse
</div>
<div class="mb-10">
    <x-planning-form.item-add-button type="button" wire:click="addSavingItem({{ $res_savings_items }})">
        {{ __('Add Item') }}
    </x-planning-form.item-add-button>
</div>