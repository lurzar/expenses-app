<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-red-800 dark:bg-red-200 border border-transparent rounded-md font-semibold text-xs text-gray dark:text-gray-800 uppercase tracking-widest hover:bg-red-700 dark:hover:bg-red focus:bg-red-700 dark:focus:bg-red active:bg-red-900 dark:active:bg-red-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
