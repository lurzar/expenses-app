<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight">
            <i class="fa-solid fa-money-bill-trend-up"></i>
            &nbsp;
            @lang('common.expenses')
        </h2>
    </x-slot>    
    <section id="allExpenses">
        <x-section-header 
            :title="__('common.sentence.list_expenses')" 
            :description="__('common.sentence.list_expenses_desc')"
            :show_total_balance="false"
        >
            <i class="fa-solid fa-file-lines"></i>
            &nbsp;
        </x-section-header>
        @if (blank($expenses))
            <span class="text-red-400 dark:text-red-600">@lang('common.error.data_not_found')</span>
        @else
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Month</th>
                            <th>Year</th>
                            <th>Spending</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($expenses as $expense)
                            <tr class="hover">
                                <th>
                                    <a href="{{ route('expenses.show', ['slug' => $expense->slug]) }}">
                                        Expenses {{ $expense->month }}, {{ $expense->year }}
                                    </a>
                                </th>
                                <td>
                                    {{ $expense->month }}
                                </td>
                                <td>
                                    {{ $expense->year }}
                                </td>
                                <td>
                                    RM {{ $expense->total_spent ?? 'N/A' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-3">
                    {{ $expenses->links() }}
                </div>
            </div>
        @endif
    </section>
</x-app-layout>
