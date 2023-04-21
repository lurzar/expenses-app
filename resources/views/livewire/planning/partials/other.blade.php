<div id="otherSection">
    <x-section-header 
        :title="__('common.sentence.other_information')" 
        :description="__('common.sentence.other_desc')"
        :res_total="$total_other"
        :res_balance="$balance_after_commitments"
    >
        <i class="fa-solid fa-hand-holding-dollar"></i>
        &nbsp;
    </x-section-header>

    <x-input-error :messages="$errors->get('others_amount_limit')" class="mt-2  my-6" />

    <div class="grid grid-cols-6 gap-4 mb-6 items-end">
        @forelse ($others_fields as $others_item)   
            <div class="col-span-4">
                <x-input-label for="others_values.{{ $others_item }}.item" :value="__('label.item')" />
                <x-text-input 
                    id="others_values.{{ $others_item }}.item" 
                    class="block mt-1 w-full" 
                    type="text" 
                    name="others_values[{{ $others_item }}][item]" 
                    wire:model="others_values.{{ $others_item }}.item" 
                    placeholder="{{ __('label.other_placeholder') }}" 
                    required 
                    autofocus 
                />
                <x-input-error :messages="$errors->get('others_values.'.$others_item.'.item')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="others_values.{{ $others_item }}.amount" :value="__('label.amount')" />
                <x-text-input 
                    id="others_values.{{ $others_item }}.amount" 
                    class="block mt-1 w-full" 
                    type="number" 
                    name="others_values[{{ $others_item }}][amount]" 
                    wire:model="others_values.{{ $others_item }}.amount" 
                    min="1.00" 
                    step="any" 
                    placeholder="0.00" 
                    required 
                    autofocus 
                />
                <x-input-error :messages="$errors->get('others_values.'.$others_item.'.amount')" class="mt-2" />
            </div>
            @if ($others_item != 0)
                <div>
                    <x-planning-form.item-remove-button type="button" wire:click="removeOtherField({{ $others_item }})" />
                </div>
            @endif
        @empty
            <div class="col-span-5 text-red-700 dark:text-red-500">
                {{ __('Error occured while fetching expenses items field') }}
            </div>
        @endforelse
    </div>
    <div class="mb-10">
        <x-planning-form.item-add-button type="button" wire:click="addOtherField({{ $others_fields }})" />
    </div>
</div>