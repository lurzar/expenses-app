<section id="allExpenses">
    <x-section-header 
        :title="__('common.sentence.list_expenses')" 
        :description="__('common.sentence.list_expenses_desc')"
        :show_total_balance="false"
    >
        <i class="fa-solid fa-file-lines"></i>
        &nbsp;
    </x-section-header>
    @if (!blank($expenses))
        <div class="relative overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400 mb-6">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">
                            Name
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Month
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Year
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Spending
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($expenses as $expense)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                <a target="_blank" href="{{ route('expenses.show', ['slug' => $expense->slug]) }}">
                                    Expenses {{ $expense->month }}, {{ $expense->year }}
                                </a>
                            </th>
                            <td class="px-6 py-4">
                                {{ $expense->month }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $expense->year }}
                            </td>
                            <td class="px-6 py-4">
                                @if (!blank($expense->totals))
                                    RM {{ array_sum($expense->totals) }}
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                    @empty
                        <span class="text-red-400 dark:text-red-600">@lang('common.error.data_not_found')</span>
                    @endforelse
                </tbody>
            </table>
            {{ $expenses->links() }}
        </div>
    @else
        <span class="text-red-400 dark:text-red-600">@lang('common.error.expenses_list_table')</span>
    @endif
</section>