<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        @lang('common.dashboard')
                    </x-nav-link>
                    <x-nav-link :href="route('planning.index')" :active="request()->routeIs('planning.index')">
                        @lang('common.planning')
                    </x-nav-link>
                    <x-nav-link :href="route('expenses.index')" :active="request()->routeIs('expenses.index')">
                        @lang('common.expenses')
                    </x-nav-link>
                </div>
            </div>

            <!-- Menu Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6 gap-x-2">
                <!-- Themes Mode Dropdown -->
                <x-compiled.button-theme-mode />

                <!-- Languages Mode Dropdown -->
                <x-compiled.button-language-mode />
                
                <!-- Settings Dropdown -->
                <x-compiled.button-settings />
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                @lang('common.dashboard')
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('planning.index')" :active="request()->routeIs('planning.index')">
                @lang('common.planning')
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('expenses.index')" :active="request()->routeIs('expenses.index')">
                @lang('common.expenses')
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">
                    <i class="fa-solid fa-gear"></i>
                    &nbsp;
                    @lang('common.settings')
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    <i class="fa-solid fa-user"></i>
                    &nbsp;
                    @lang('common.profile')
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault();this.closest('form').submit();">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        &nbsp;
                        @lang('common.logout')
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>

        <!-- Responsive Languages Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">
                    <i class="fa-solid fa-language"></i>
                    &nbsp;
                    @lang('common.language')
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('language', 'en')">
                    @lang('common.english')
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('language', 'my')">
                    @lang('common.malay')
                </x-responsive-nav-link>
            </div>
        </div>
    </div>
</nav>
