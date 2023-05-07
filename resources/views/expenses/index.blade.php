<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            <i class="fa-solid fa-money-bill-trend-up"></i>
            &nbsp;
            @lang('common.expenses')
        </h2>
    </x-slot>

    <div class="pt-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{-- <x-expense.tables.all :expenses="$expenses"/> --}}
                    {{-- <ul role="list" class="divide-y divide-gray-100">
                        <li class="flex justify-between gap-x-6 py-5">
                          <div class="flex gap-x-4">
                            <img class="h-12 w-12 flex-none rounded-full bg-gray-50" src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
                            <div class="min-w-0 flex-auto">
                              <p class="text-sm font-semibold leading-6 text-gray-900">Leslie Alexander</p>
                              <p class="mt-1 truncate text-xs leading-5 text-gray-500">leslie.alexander@example.com</p>
                            </div>
                          </div>
                          <div class="hidden sm:flex sm:flex-col sm:items-end">
                            <p class="text-sm leading-6 text-gray-900">Co-Founder / CEO</p>
                            <p class="mt-1 text-xs leading-5 text-gray-500">Last seen <time datetime="2023-01-23T13:23Z">3h ago</time></p>
                          </div>
                        </li>
                        <li class="flex justify-between gap-x-6 py-5">
                          <div class="flex gap-x-4">
                            <img class="h-12 w-12 flex-none rounded-full bg-gray-50" src="https://images.unsplash.com/photo-1519244703995-f4e0f30006d5?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
                            <div class="min-w-0 flex-auto">
                              <p class="text-sm font-semibold leading-6 text-gray-900">Michael Foster</p>
                              <p class="mt-1 truncate text-xs leading-5 text-gray-500">michael.foster@example.com</p>
                            </div>
                          </div>
                          <div class="hidden sm:flex sm:flex-col sm:items-end">
                            <p class="text-sm leading-6 text-gray-900">Co-Founder / CTO</p>
                            <p class="mt-1 text-xs leading-5 text-gray-500">Last seen <time datetime="2023-01-23T13:23Z">3h ago</time></p>
                          </div>
                        </li>
                        <li class="flex justify-between gap-x-6 py-5">
                          <div class="flex gap-x-4">
                            <img class="h-12 w-12 flex-none rounded-full bg-gray-50" src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
                            <div class="min-w-0 flex-auto">
                              <p class="text-sm font-semibold leading-6 text-gray-900">Dries Vincent</p>
                              <p class="mt-1 truncate text-xs leading-5 text-gray-500">dries.vincent@example.com</p>
                            </div>
                          </div>
                          <div class="hidden sm:flex sm:flex-col sm:items-end">
                            <p class="text-sm leading-6 text-gray-900">Business Relations</p>
                            <div class="mt-1 flex items-center gap-x-1.5">
                              <div class="flex-none rounded-full bg-emerald-500/20 p-1">
                                <div class="h-1.5 w-1.5 rounded-full bg-emerald-500"></div>
                              </div>
                              <p class="text-xs leading-5 text-gray-500">Online</p>
                            </div>
                          </div>
                        </li>
                        <li class="flex justify-between gap-x-6 py-5">
                          <div class="flex gap-x-4">
                            <img class="h-12 w-12 flex-none rounded-full bg-gray-50" src="https://images.unsplash.com/photo-1517841905240-472988babdf9?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
                            <div class="min-w-0 flex-auto">
                              <p class="text-sm font-semibold leading-6 text-gray-900">Lindsay Walton</p>
                              <p class="mt-1 truncate text-xs leading-5 text-gray-500">lindsay.walton@example.com</p>
                            </div>
                          </div>
                          <div class="hidden sm:flex sm:flex-col sm:items-end">
                            <p class="text-sm leading-6 text-gray-900">Front-end Developer</p>
                            <p class="mt-1 text-xs leading-5 text-gray-500">Last seen <time datetime="2023-01-23T13:23Z">3h ago</time></p>
                          </div>
                        </li>
                        <li class="flex justify-between gap-x-6 py-5">
                          <div class="flex gap-x-4">
                            <img class="h-12 w-12 flex-none rounded-full bg-gray-50" src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
                            <div class="min-w-0 flex-auto">
                              <p class="text-sm font-semibold leading-6 text-gray-900">Courtney Henry</p>
                              <p class="mt-1 truncate text-xs leading-5 text-gray-500">courtney.henry@example.com</p>
                            </div>
                          </div>
                          <div class="hidden sm:flex sm:flex-col sm:items-end">
                            <p class="text-sm leading-6 text-gray-900">Designer</p>
                            <p class="mt-1 text-xs leading-5 text-gray-500">Last seen <time datetime="2023-01-23T13:23Z">3h ago</time></p>
                          </div>
                        </li>
                        <li class="flex justify-between gap-x-6 py-5">
                          <div class="flex gap-x-4">
                            <img class="h-12 w-12 flex-none rounded-full bg-gray-50" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
                            <div class="min-w-0 flex-auto">
                              <p class="text-sm font-semibold leading-6 text-gray-900">Tom Cook</p>
                              <p class="mt-1 truncate text-xs leading-5 text-gray-500">tom.cook@example.com</p>
                            </div>
                          </div>
                          <div class="hidden sm:flex sm:flex-col sm:items-end">
                            <p class="text-sm leading-6 text-gray-900">Director of Product</p>
                            <div class="mt-1 flex items-center gap-x-1.5">
                              <div class="flex-none rounded-full bg-emerald-500/20 p-1">
                                <div class="h-1.5 w-1.5 rounded-full bg-emerald-500"></div>
                              </div>
                              <p class="text-xs leading-5 text-gray-500">Online</p>
                            </div>
                          </div>
                        </li>
                    </ul> --}}
                    <section class="py-28 bg-gray-900">
                        <div class="max-w-screen-xl mx-auto px-4 md:px-8">
                            <div class="max-w-2xl mx-auto text-center">
                                <h3 class="text-white text-3xl font-semibold sm:text-4xl">
                                    Our customers are always happy
                                </h3>
                                <p class="mt-3 text-gray-300">
                                    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi venenatis sollicitudin quam ut tincidunt.
                                </p>
                            </div>
                            <div class="mt-12">
                                <ul class="flex flex-col gap-4 items-center justify-center sm:flex-row">
                                    <li key="1" class="w-full text-center bg-gray-800 px-12 py-4 rounded-lg sm:w-auto">
                                        <h4 class="text-4xl text-white font-semibold">35K</h4>
                                        <p class="mt-3 text-gray-400 font-medium">Customers</p>
                                    </li>
                                    <li key="2" class="w-full text-center bg-gray-800 px-12 py-4 rounded-lg sm:w-auto">
                                        <h4 class="text-4xl text-white font-semibold">40+</h4>
                                        <p class="mt-3 text-gray-400 font-medium">Countries</p>
                                    </li>
                                    <li key="3" class="w-full text-center bg-gray-800 px-12 py-4 rounded-lg sm:w-auto">
                                        <h4 class="text-4xl text-white font-semibold">30M+</h4>
                                        <p class="mt-3 text-gray-400 font-medium">Total revenue</p>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
