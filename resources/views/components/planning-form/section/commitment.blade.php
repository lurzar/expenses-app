@props(['res_commitments_items' => []])


<div class="grid grid-cols-6 gap-4 mb-6 items-end">
    @forelse ($res_commitments_items as $commitments_item)   
        <div class="col-span-4">
            <x-input-label for="commitments_values.{{ $commitments_item }}.item" :value="__('Item')" />
            <x-text-input 
                id="commitments_values.{{ $commitments_item }}.item" 
                class="block mt-1 w-full" 
                type="text" 
                name="commitments_values.{{ $commitments_item }}.item" 
                wire:model="commitments_values.{{ $commitments_item }}.item" 
                :value="old('commitments_values.'.$commitments_item.'.item')" 
                placeholder="e.g. Loans, Insurances" 
                required 
                autofocus 
            />
            <x-input-error :messages="$errors->get('commitments_values.'.$commitments_item.'.item')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="commitments_values.{{ $commitments_item }}.amount" :value="__('Amount (RM)')" />
            <x-text-input 
                id="commitments_values.{{ $commitments_item }}.amount" 
                class="block mt-1 w-full" 
                type="number" 
                name="commitments_values.{{ $commitments_item }}.amount"
                wire:model="commitments_values.{{ $commitments_item }}.amount"  
                min="1.00" 
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
                <x-planning-form.item-remove-button type="button" wire:click="removeCommitmentItem({{ $commitments_item }})" />
            </div>
        @endif
    @empty
        <div class="col-span-5 text-red-700 dark:text-red-500">
            {{ __('Error occured while fetching expenses items field') }}
        </div>
    @endforelse
</div>
<div class="mb-10">
    <x-planning-form.item-add-button type="button" wire:click="addCommitmentItem({{ $res_commitments_items }})" />
</div>