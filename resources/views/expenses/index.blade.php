<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight">
            <i class="fa-solid fa-money-bill-trend-up"></i>
            &nbsp;
            @lang('common.expenses')
        </h2>
    </x-slot>    
    <section id="expensesList">
        <div class="mb-6 flow-root">
            <div class="float-left">
                <x-section-header 
                    :title="__('common.sentence.list_expenses')" 
                    :description="__('common.sentence.list_expenses_desc')"
                >
                    <i class="fa-solid fa-file-lines"></i>
                    &nbsp;
                </x-section-header>
            </div>
        </div>
        <x-table :datasets="$expenses"/>
    </section>
</x-app-layout>
