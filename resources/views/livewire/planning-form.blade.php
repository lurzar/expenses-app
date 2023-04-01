<div>
    <form action="{{ route('planning.store') }}" method="POST">
        @csrf

        <div id="salarySection">
            <x-planning-form.section.header 
                :title="__('Salary Information')" 
                :description="__('Provide your salary information and savings percentages.')"
                :custom="false"
            />
            <x-planning-form.section.salary />
        </div>

        <div id="savingSection">
            <x-planning-form.section.header 
                :title="__('Savings Information')" 
                :description="__('Provide your salary information and savings percentages.')"
                :res_total_saving="$total_savings"
                :res_balance="$balance_after_saving_rates"
            />
            <x-planning-form.section.saving 
                :res_savings_items="$savings_items"
                :res_savings_values="$savings_values"
            />
        </div>

        <div id="commitmentSection">
            <x-planning-form.section.header 
                :title="__('Commitment Expenses Information')" 
                :description="__('Provide your commitment expenses information with appropriate amount.')"
                :res_balance="$balance_after_savings"
            />
            <x-planning-form.section.commitment 
                :res_commitments_items="$commitments_items"
                :res_commitments_values="$commitments_values" 
            />
        </div>

        <div id="otherSection">
            <x-planning-form.section.header 
                :title="__('Other Expenses Information')" 
                :description="__('Provide your other expenses information with appropriate amount.')"
                :res_balance="$balance_after_commitments"
            />
            <x-planning-form.section.other 
                :res_others_items="$others_items"
                :res_others_values="$others_values" 
            />
        </div>

        <div id="summarySection">
            <x-planning-form.section.header 
                :title="__('Planning Summary')" 
                :description="__('Summaries of your expenses planning.')"
                :custom="false"
            />
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
        </div>

        {{-- Button submit --}}
        <div class="mb-6 float-right">
            <x-primary-button type="submit">
                {{ __('Submit') }}
            </x-primary-button>
        </div>
    </form>
</div>