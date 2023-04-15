<x-dropdown align="right" width="48">
    <x-slot name="trigger">
        <button class="inline-flex items-center px-3 py-2 border border-transparent text-lg leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
            <div>
                <i class="fa-solid fa-language"></i>
            </div>
        </button>
    </x-slot>

    <x-slot name="content">
        <x-dropdown-link :href="route('language', 'en')">
            {{ __('common.english') }}
        </x-dropdown-link>
        <x-dropdown-link :href="route('language', 'my')">
            {{ __('common.malay') }}
        </x-dropdown-link>
    </x-slot>
</x-dropdown>