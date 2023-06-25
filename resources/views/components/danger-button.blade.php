@props(['icon' => ''])

<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-error']) }}>
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
            @elseif ($icon == 'create')
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="stroke-current w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
            @elseif ($icon == 'add')
                <i class="fa-solid fa-plus"></i>
            @else
                ? 
            @endif
        </span>
    @endif
    
    {{ $slot }}
</button>
