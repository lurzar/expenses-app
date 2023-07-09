@props(['active'])

<a {{ $attributes->merge(['class' => $active ? 'active' : '']) }}>
    {{ $slot }}
</a>
