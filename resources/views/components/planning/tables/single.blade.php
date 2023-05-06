<section id="currentMonthPlanning">
    <x-section-header 
        :title="__('common.sentence.latest_planning')" 
        :description="__('common.sentence.latest_planning_desc')"
        :show_total_balance="false"
    >
        <i class="fa-solid fa-file-lines"></i>
        &nbsp;
    </x-section-header>
    <div class="relative overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">
                        Name
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Section
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
                            @forelse ($planning->sections as $key => $section)
                                {{ $key }}
                                <br />
                                @forelse ($section as $key => $item)
                                    {{  $key + 1 }}. Item: {{ $item['item'] }}
                                    <br /> 
                                    &nbsp;&nbsp; Amount: RM{{ $item['amount'] }}
                                @empty
                                    No section detail found
                                @endforelse
                                <br />
                                <br />
                            @empty
                                No section data found
                            @endforelse
                        </td>
                    </tr>
                @empty
                    Data Not Found
                @endforelse
            </tbody>
        </table>
    </div>
</section>