<div id="salarySection">
    <x-section-header 
        :title="__('common.sentence.salary_information')" 
        :description="__('common.sentence.salary_desc')"
        :show_total_balance="false"
    >
        <i class="fa-solid fa-sack-dollar"></i>
        &nbsp;
    </x-section-header>
    
    <div class="grid grid-cols-2 gap-4 mb-10">
        <div>
            <x-input-label for="current_month" :value="__('label.month')" />
            <x-text-input id="current_month" type="text" name="current_month" wire:model="current_month" :disabled="true" required autofocus />
        </div>
        <div>
            <x-input-label for="current_year" :value="__('label.year')" />
            <x-text-input id="current_year" type="text" name="current_year" wire:model="current_year" :disabled="true" required autofocus  />
        </div>
        <div>
            <x-input-label for="salary" :value="__('label.salary')" />
            <x-text-input 
                id="salary" 
                type="number" 
                name="salary" 
                min="0.00" 
                step="any" 
                wire:model="salary"
                placeholder="0.00" 
                required 
                autofocus 
            />
            <x-input-error :messages="$errors->get('salary')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="saving_rate" :value="__('label.saving')" />
            <x-text-input 
                id="saving_rate" 
                type="number" 
                name="saving_rate" 
                min="20" 
                max="100" 
                step="5" 
                wire:model="saving_rate" 
                required 
                autofocus 
            />
            <x-input-error :messages="$errors->get('saving_rate')" class="mt-2" />
        </div>
    </div>
</div>