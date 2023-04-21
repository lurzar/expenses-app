<div id="summarySection">
    <x-section-header 
        :title="__('common.sentence.planning_summary')" 
        :description="__('common.sentence.planning_summary_desc')"
        :custom="false"
    >
        <i class="fa-solid fa-file-invoice-dollar"></i>
        &nbsp;
    </x-section-header>

    <div class="grid grid-cols-2 gap-4 mb-10">
        <div>
            <x-input-label for="total_savings" :value="__('label.total_saving')" />
            <x-text-input 
                id="total_savings" 
                class="block mt-1 w-full" 
                type="text" 
                name="total_savings" 
                wire:model="total_savings"
                :value="old('total_savings')" 
                :disabled="true" 
            />
            <x-input-error :messages="$errors->get('total_savings')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="total_balance" :value="__('label.total_balance')" />
            <x-text-input 
                id="total_balance" 
                class="block mt-1 w-full" 
                type="text" 
                name="total_balance" 
                wire:model="total_balance"
                :value="old('total_balance')" 
                :disabled="true" 
            />
            <x-input-error :messages="$errors->get('total_balance')" class="mt-2" />
        </div>
    </div>
</div>