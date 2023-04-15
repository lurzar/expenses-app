<x-dropdown align="right" width="48">
    <x-slot name="trigger">
        <button class="inline-flex items-center px-3 py-2 border border-transparent text-lg leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
            <div>
                <i class="fa-solid fa-gear"></i>
            </div>
        </button>
    </x-slot>

    <x-slot name="content">
        <x-dropdown-link :href="route('profile.edit')">
            <i class="fa-solid fa-user"></i>
            &nbsp;
            @lang('common.profile')
        </x-dropdown-link>

        <!-- Authentication -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault();this.closest('form').submit();">
                <i class="fa-solid fa-right-from-bracket"></i>
                &nbsp;
                @lang('common.logout')
            </x-dropdown-link>
        </form>
    </x-slot>
</x-dropdown>