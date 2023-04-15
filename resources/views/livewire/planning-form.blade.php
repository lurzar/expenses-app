<div>
    <form action="{{ route('planning.store') }}" method="POST">
        @csrf

        <div id="salarySection">
            <x-planning-form.section.header 
                :title="__('common.sentence.salary_information')" 
                :description="__('common.sentence.salary_desc')"
                :show_balance="false"
            />
            <x-planning-form.section.salary />
        </div>

        <div id="savingSection">
            <x-planning-form.section.header 
                :title="__('common.sentence.saving_information')" 
                :description="__('common.sentence.saving_desc')"
                :show_saving="true"
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
                :title="__('common.sentence.commitment_information')" 
                :description="__('common.sentence.commitment_desc')"
                :res_balance="$balance_after_savings"
            />
            <x-planning-form.section.commitment 
                :res_commitments_items="$commitments_items"
                :res_commitments_values="$commitments_values" 
            />
        </div>

        <div id="otherSection">
            <x-planning-form.section.header 
                :title="__('common.sentence.other_information')" 
                :description="__('common.sentence.other_desc')"
                :res_balance="$balance_after_commitments"
            />
            <x-planning-form.section.other 
                :res_others_items="$others_items"
                :res_others_values="$others_values" 
            />
        </div>

        <div id="summarySection">
            <x-planning-form.section.header 
                :title="__('common.sentence.planning_summary')" 
                :description="__('common.sentence.planning_summary_desc')"
                :custom="false"
            />
            <div class="grid grid-cols-2 gap-4 mb-10">
                <div>
                    <x-input-label for="total-saving" :value="__('label.total_saving')" />
                    <x-text-input id="total-saving" class="block mt-1 w-full" type="text" name="total-saving" :value="old('total-saving')" :disabled="true" />
                    <x-input-error :messages="$errors->get('total-saving')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="total-change" :value="__('label.total_balance')" />
                    <x-text-input id="total-change" class="block mt-1 w-full" type="text" name="total-change" :value="old('total-change')" :disabled="true" />
                    <x-input-error :messages="$errors->get('total-change')" class="mt-2" />
                </div>
            </div>
        </div>

        {{-- Button submit --}}
        <div class="mb-6 float-right">
            <x-primary-button type="submit">
                @lang('common.submit')
            </x-primary-button>
        </div>
    </form>
</div>