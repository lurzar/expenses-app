<div id="commitmentSection">
    <x-section-header 
        :title="__('common.sentence.commitment_information')" 
        :description="__('common.sentence.commitment_desc')"
        :res_total="$total_commitment"
        :res_balance="$balance_after_savings"
    >
        <i class="fa-solid fa-credit-card"></i>
        &nbsp;
    </x-section-header>

    <x-input-error :messages="$errors->get('commitments_amount_limit')" class="mt-2  my-6" />

    <div class="grid grid-cols-6 gap-4 mb-6 items-end">
        @forelse ($commitments_fields as $commitments_item)   
            <div class="col-span-4">
                <x-input-label for="commitments_values.{{ $commitments_item }}.item" :value="__('label.item')" />
                <x-text-input 
                    id="commitments_values.{{ $commitments_item }}.item" 
                    class="block mt-1 w-full" 
                    type="text" 
                    name="commitments_values.{{ $commitments_item }}.item" 
                    wire:model="commitments_values.{{ $commitments_item }}.item" 
                    :value="old('commitments_values.'.$commitments_item.'.item')" 
                    placeholder="{{ __('label.commitment_placeholder') }}" 
                    required 
                    autofocus 
                />
                <x-input-error :messages="$errors->get('commitments_values.'.$commitments_item.'.item')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="commitments_values.{{ $commitments_item }}.amount" :value="__('label.amount')" />
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
                    <x-planning-form.item-remove-button type="button" wire:click="removeCommitmentField({{ $commitments_item }})" />
                </div>
            @endif
        @empty
            <div class="col-span-5 text-red-700 dark:text-red-500">
                @lang('common.error.commitment_field')
            </div>
        @endforelse
    </div>
    <div class="mb-10">
        <x-planning-form.item-add-button type="button" wire:click="addCommitmentField({{ $commitments_fields }})" />
    </div>
</div>