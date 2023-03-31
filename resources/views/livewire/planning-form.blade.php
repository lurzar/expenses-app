<div>
    <form action="{{ route('planning.store') }}" method="POST">
        @csrf

        <x-planning-form.section.salary 
            :current_month="$current_month" 
            :current_year="$current_year" 
            :salary="$salary" 
            :saving_rate="$saving_rate" 
        />

        <x-planning-form.section.saving 
            :balance_after_saving_rates="$balance_after_saving_rates"
            :savings_items="$savings_items"
            :savings_values="$savings_values" 
        />
        
        <x-planning-form.section.commitment 
            :balance_after_savings="$balance_after_savings"
            :commitments_items="$commitments_items"
            :commitments_values="$commitments_values" 
        />

        <x-planning-form.section.other 
            :balance_after_commitments="$balance_after_commitments"
            :others_items="$others_items"
            :others_values="$others_values" 
        />

        {{-- Planning summary --}}
        <header class="mb-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Planning Summary') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __("Summaries of your expenses planning.") }}
            </p>
        </header>

        <hr class="h-px my-6 w-80 bg-gray-200 border-0 dark:bg-gray-700">

        <div class="grid grid-cols-2 gap-4 mb-10">
            <div>
                <x-input-label for="total-saving" :value="__('Total Saving (RM)')" />
                <x-text-input id="total-saving" class="block mt-1 w-full" type="text" name="total-saving" :value="old('total-saving')" :disabled="true" />
                <x-input-error :messages="$errors->get('total-saving')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="total-change" :value="__('Total Change (RM)')" />
                <x-text-input id="total-change" class="block mt-1 w-full" type="text" name="total-change" :value="old('total-change')" :disabled="true" />
                <x-input-error :messages="$errors->get('total-change')" class="mt-2" />
            </div>
        </div>

        {{-- Button submit --}}
        <div class="mb-6 float-right">
            <x-primary-button type="submit">
                {{ __('Submit') }}
            </x-primary-button>
        </div>
    </form>
</div>