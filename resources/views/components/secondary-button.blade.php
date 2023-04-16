@props(['icon' => ''])

<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150']) }}>
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
