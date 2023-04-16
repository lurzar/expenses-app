@props(['icon' => ''])

<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150']) }}>
    @if (! blank($icon))
        <span class="text-xs @if($slot->isNotEmpty()) pr-2 @endif">
            @if ($icon == 'save')
                <i class="fa-solid fa-floppy-disk"></i>
            @elseif ($icon == 'confirm')
                <i class="fa-solid fa-circle-check"></i>
            @elseif ($icon == 'submit')
                <i class="fa-solid fa-paper-plane"></i>
            @elseif ($icon == 'login')
                <i class="fa-solid fa-right-to-bracket"></i>
            @elseif ($icon == 'update')
                <i class="fa-solid fa-rotate"></i>
            @elseif ($icon == 'cancel')
                <i class="fa-solid fa-ban"></i>
            @elseif ($icon == 'delete')
                <i class="fa-solid fa-trash-can"></i>
            @endif
        </span>
    @endif

    {{ $slot }}
</button>
