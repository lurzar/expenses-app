@props(['inject' => 'currently service get all expenses unavailable'])

<div>
    <div class="pt-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if ($inject->isNotEmpty())
                        <section id="thisMonthPlanning">
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
                                                Salary
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($inject as $planning)
                                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                    <span
                                                        class="cursor-pointer"
                                                        x-data=""
                                                        x-on:click.prevent="$dispatch('open-modal', 'show-expenses')"
                                                    >
                                                        Planning {{ $planning->month }}, {{ $planning->year }}
                                                    </span>
                                                </th>
                                                <td class="px-6 py-4">
                                                    {{ $planning->month }}
                                                </td>
                                                <td class="px-6 py-4">
                                                    {{ $planning->year }}
                                                </td>
                                                <td class="px-6 py-4">
                                                    {{ $planning->salary }}
                                                </td>
                                            </tr>
                                        @empty
                                            Data Not Found
                                        @endforelse
                                    </tbody>
                                </table>
                                {{-- <x-modal name="show-expenses" focusable>
                                    <div class="p-4">
                                        <div class="mt-6 flex justify-end">
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
                                                    @forelse ($inject as $planning)
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
                                        <div class="mt-6 flex justify-end">
                                            <x-secondary-button x-on:click="$dispatch('close')" :icon="'cancel'">
                                                @lang('common.close')
                                            </x-secondary-button>
                                        </div>
                                    </div>
                                </x-modal> --}}

                                <x-modal name="show-expenses" focusable>
                                    <div class="p-4">
                                        <div class="mt-6 flex justify-end">
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
                                                    @forelse ($planning->sections as $key => $sections)
                                                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                                            <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                                Planningl {{ $planning->month }}, {{ $planning->year }}
                                                            </th>
                                                            <td class="px-6 py-4">
                                                                @forelse ($sections as $key => $section)
                                                                    {{ $key }}
                                                                    <br />
                                                                    @forelse ($section as $key => $item)
                                                                        {{  (int) $key + 1 }}. Item: {{ $item }}
                                                                        <br /> 
                                                                        &nbsp;&nbsp; Amount: RM{{ $item }}
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
                                        <div class="mt-6 flex justify-end">
                                            <x-secondary-button x-on:click="$dispatch('close')" :icon="'cancel'">
                                                @lang('common.close')
                                            </x-secondary-button>
                                        </div>
                                    </div>
                                </x-modal>
                            </div>
                        </section>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>