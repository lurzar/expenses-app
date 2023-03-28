<div>
    {{-- Salary information --}}
    <header class="mb-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Salary Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Provide your salary's information and savings percentages.") }}
        </p>
    </header>

    <hr class="h-px my-6 w-80 bg-gray-200 border-0 dark:bg-gray-700">

    <div class="grid grid-cols-2 gap-4 mb-10">
        <div>
            <x-input-label for="current_month" :value="__('Month')" />
            <x-text-input id="current_month" class="block mt-1 w-full dark:text-gray-500" type="text" name="current_month" :value="$current_month" :disabled="true" required autofocus />
        </div>
        <div>
            <x-input-label for="current_year" :value="__('Year')" />
            <x-text-input id="current_year" class="block mt-1 w-full dark:text-gray-500" type="text" name="current_year" :value="$current_year" :disabled="true" required autofocus  />
        </div>
        <div>
            <x-input-label for="salary" :value="__('Salary (RM)')" />
            <x-text-input 
                id="salary" 
                class="block mt-1 w-full" 
                type="number" 
                name="salary" 
                min="0.00" 
                step="any" 
                wire:model="salary"
                :value="old('salary')" 
                placeholder="0.00" 
                required 
                autofocus 
            />
            <x-input-error :messages="$errors->get('salary')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="saving" :value="__('Saving (%)')" />
            <x-text-input 
                id="saving" 
                class="block mt-1 w-full" 
                type="number" 
                name="saving" 
                min="20" 
                step="5" 
                wire:model="saving_rate" 
                :value="old('saving_rate')"  
                required 
                autofocus 
            />
            <x-input-error :messages="$errors->get('saving_rate')" class="mt-2" />
        </div>
    </div>
</div>
