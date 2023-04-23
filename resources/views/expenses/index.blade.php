<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            <i class="fa-solid fa-money-bill-trend-up"></i>
            &nbsp;
            @lang('common.expenses')
        </h2>
    </x-slot>

    {{-- <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("You're on expenses page!") }}
                </div>
            </div>
        </div>
    </div> --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- <section id="expensesSection">
                        @livewire('expenses', ['plannings' => $plannings])
                    </section> --}}
                    
                    <div class="relative overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
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
                                        Slug
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Salary
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($plannings as $planning)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            Planning {{ $planning->month }}, {{ $planning->year }}
                                        </th>
                                        <td class="px-6 py-4">
                                            {{ $planning->month }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $planning->year }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <a target="_blank" href="{{ route('expenses.show', ['slug' => $planning->slug]) }}">{{ $planning->slug }}</a>
                                        </th>
                                        <td class="px-6 py-4">
                                            {{ $planning->salary }}
                                        </td>
                                    </tr>
                                @empty
                                    Data Not Found
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
