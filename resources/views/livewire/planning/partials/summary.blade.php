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