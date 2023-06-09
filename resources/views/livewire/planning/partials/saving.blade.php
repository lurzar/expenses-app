<div id="savingSection">
    <x-section-header 
        :title="__('common.sentence.saving_information')" 
        :description="__('common.sentence.saving_desc')"
        :res_total="$total_savings"
        :res_balance="$balance_after_saving_rates"
        :show_total_balance="true"
    >
        <i class="fa-solid fa-circle-dollar-to-slot"></i>
        &nbsp;
    </x-section-header>

    <x-input-error :messages="$errors->get('savings_amount_limit')" class="mt-2 my-6" />

    <div class="grid grid-cols-6 gap-4 mb-6">
        @forelse ($savings_fields as $savings_field)   
            <div class="col-span-4">
                <x-input-label for="savings_values.{{ $savings_field }}.item" :value="__('label.item')" />
                <x-text-input 
                    id="savings_values.{{ $savings_field }}.item" 
                    type="text" 
                    name="savings_values[{{ $savings_field }}][item]" 
                    wire:model="savings_values.{{ $savings_field }}.item" 
                    placeholder="{{ __('label.saving_placeholder') }}" 
                    required 
                    autofocus 
                />
                <x-input-error :messages="$errors->get('savings_values.'.$savings_field.'.item')" class="mt-2" />
            </div>
            <div class="col-span-1">
                <x-input-label for="savings_values.{{ $savings_field }}.amount" :value="__('label.amount')" />
                <x-text-input 
                    id="savings_values.{{ $savings_field }}.amount" 
                    type="number" 
                    name="savings_values[{{ $savings_field }}][amount]"
                    wire:model="savings_values.{{ $savings_field }}.amount"  
                    min="1.00" 
                    step="any" 
                    placeholder="0.00" 
                    required 
                    autofocus 
                />
                <x-input-error :messages="$errors->get('savings_values.'.$savings_field.'.amount')" class="mt-2" />
            </div>
            @if ($savings_field != 0)
                <div class="col-span-1">
                    <div class="pt-9">
                        <x-danger-button type="button" :icon="'delete'" wire:click="removeSavingField({{ $savings_field }})">
                            @lang('common.remove')
                        </x-danger-button>
                    </div>
                </div>
            @endif
        @empty
            <div class="col-span-5 text-red-700 dark:text-red-500">
                @lang('common.error.saving_field')
            </div>
        @endforelse
    </div>
    <div class="mb-10">
        <x-secondary-button type="button" :icon="'add'" wire:click="addSavingField({{ $savings_fields }})">
            @lang('common.add')
        </x-secondary-button>
    </div>
</div>