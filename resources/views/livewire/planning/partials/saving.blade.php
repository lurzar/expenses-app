<div id="savingSection">
    <x-section-header 
        :title="__('common.sentence.saving_information')" 
        :description="__('common.sentence.saving_desc')"
        :show_saving="true"
        :res_total_saving="$total_savings"
        :res_balance="$balance_after_saving_rates"
    >
        <i class="fa-solid fa-circle-dollar-to-slot"></i>
        &nbsp;
    </x-section-header>

    <x-input-error :messages="$errors->get('savings_amount_limit')" class="mt-2  my-6" />

    <div class="grid grid-cols-6 gap-4 mb-6 items-end">
        @forelse ($savings_fields as $savings_field)   
            <div class="col-span-4">
                <x-input-label for="savings_values.{{ $savings_field }}.item" :value="__('label.item')" />
                <x-text-input 
                    id="savings_values.{{ $savings_field }}.item" 
                    class="block mt-1 w-full" 
                    type="text" 
                    name="savings_values.{{ $savings_field }}.item" 
                    wire:model="savings_values.{{ $savings_field }}.item" 
                    :value="old('savings_values.'.$savings_field.'.item')" 
                    placeholder="{{ __('label.saving_placeholder') }}" 
                    required 
                    autofocus 
                />
                <x-input-error :messages="$errors->get('savings_values.'.$savings_field.'.item')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="savings_values.{{ $savings_field }}.amount" :value="__('label.amount')" />
                <x-text-input 
                    id="savings_values.{{ $savings_field }}.amount" 
                    class="block mt-1 w-full" 
                    type="number" 
                    name="savings_values.{{ $savings_field }}.amount"
                    wire:model="savings_values.{{ $savings_field }}.amount"  
                    min="1.00" 
                    step="any" 
                    :value="old('savings_values.'.$savings_field.'.amount')" 
                    placeholder="0.00" 
                    required 
                    autofocus 
                />
                <x-input-error :messages="$errors->get('savings_values.'.$savings_field.'.amount')" class="mt-2" />
            </div>
            @if ($savings_field != 0)
                <div>
                    <x-planning-form.item-remove-button type="button" wire:click="removeSavingField({{ $savings_field }})" />
                </div>
            @endif
        @empty
            <div class="col-span-5 text-red-700 dark:text-red-500">
                @lang('common.error.saving_field')
            </div>
        @endforelse
    </div>
    <div class="mb-10">
        <x-planning-form.item-add-button type="button" wire:click="addSavingField({{ $savings_fields }})" />
    </div>
</div>