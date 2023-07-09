<div id="otherSection">
    <x-section-header 
        :title="__('common.sentence.other_information')" 
        :description="__('common.sentence.other_desc')"
        :res_total="$total_other"
        :res_balance="$balance_after_commitments"
        :show_total_balance="true"
    >
        <i class="fa-solid fa-hand-holding-dollar"></i>
        &nbsp;
    </x-section-header>

    <x-input-error :messages="$errors->get('others_amount_limit')" class="mt-2  my-6" />

    <div class="grid grid-cols-6 gap-4 mb-6">
        @forelse ($others_fields as $others_item)   
            <div class="col-span-4">
                <x-input-label for="others_values.{{ $others_item }}.item" :value="__('label.item')" />
                <x-text-input 
                    id="others_values.{{ $others_item }}.item" 
                    type="text" 
                    name="others_values[{{ $others_item }}][item]" 
                    wire:model="others_values.{{ $others_item }}.item" 
                    placeholder="{{ __('label.other_placeholder') }}" 
                    required 
                    autofocus 
                />
                <x-input-error :messages="$errors->get('others_values.'.$others_item.'.item')" class="mt-2" />
            </div>
            <div class="col-span-1">
                <x-input-label for="others_values.{{ $others_item }}.amount" :value="__('label.amount')" />
                <x-text-input 
                    id="others_values.{{ $others_item }}.amount" 
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
                <div class="col-span-1">
                    <div class="pt-9">
                        <x-danger-button type="button" :icon="'delete'" wire:click="removeOtherField({{ $others_item }})">
                            @lang('common.remove')
                        </x-danger-button>
                    </div>
                </div>
            @endif
        @empty
            <div class="col-span-5 text-red-700 dark:text-red-500">
                {{ __('Error occured while fetching expenses items field') }}
            </div>
        @endforelse
    </div>
    <div class="mb-10">
        <x-secondary-button type="button" :icon="'add'" wire:click="addOtherField({{ $others_fields }})">
            @lang('common.add')
        </x-secondary-button>
    </div>
</div>