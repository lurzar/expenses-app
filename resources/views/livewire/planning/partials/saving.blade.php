<div id="savingSection">
    <x-planning-form.header 
        :title="__('common.sentence.saving_information')" 
        :description="__('common.sentence.saving_desc')"
        :show_saving="true"
        :res_total_saving="$total_savings"
        :res_balance="$balance_after_saving_rates"
    >
        @slot('icon')
            <i class="fa-solid fa-circle-dollar-to-slot"></i>
        @endslot
    </x-planning-form.header>

    <div class="grid grid-cols-6 gap-4 mb-6 items-end">
        @forelse ($savings_items as $savings_item)   
            <div class="col-span-4">
                <x-input-label for="savings_values.{{ $savings_item }}.item" :value="__('label.item')" />
                <x-text-input 
                    id="savings_values.{{ $savings_item }}.item" 
                    class="block mt-1 w-full" 
                    type="text" 
                    name="savings_values.{{ $savings_item }}.item" 
                    wire:model="savings_values.{{ $savings_item }}.item" 
                    :value="old('savings_values.'.$savings_item.'.item')" 
                    placeholder="{{ __('label.saving_placeholder') }}" 
                    required 
                    autofocus 
                />
                <x-input-error :messages="$errors->get('savings_values.'.$savings_item.'.item')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="savings_values.{{ $savings_item }}.amount" :value="__('label.amount')" />
                <x-text-input 
                    id="savings_values.{{ $savings_item }}.amount" 
                    class="block mt-1 w-full" 
                    type="number" 
                    name="savings_values.{{ $savings_item }}.amount"
                    wire:model="savings_values.{{ $savings_item }}.amount"  
                    min="1.00" 
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
                    <x-planning-form.item-remove-button type="button" wire:click="removeSavingItem({{ $savings_item }})" />
                </div>
            @endif
        @empty
            <div class="col-span-5 text-red-700 dark:text-red-500">
                @lang('common.error.saving_field')
            </div>
        @endforelse
    </div>
    <div class="mb-10">
        <x-planning-form.item-add-button type="button" wire:click="addSavingItem({{ $savings_items }})" />
    </div>
</div>