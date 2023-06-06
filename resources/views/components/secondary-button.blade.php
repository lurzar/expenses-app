@props(['icon' => ''])

<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn btn-secondary']) }}>
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
