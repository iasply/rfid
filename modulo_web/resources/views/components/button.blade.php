@props(['type' => 'button', 'variant' => 'primary', 'fullWidth' => false])

@php
    $variantClass = match ($variant) {
    'success' => 'btn-success',
    'danger' => 'btn-danger',
    'secondary' => 'btn-secondary',
    default => 'btn-primary',
    };
@endphp

<button
    type="{{ $type }}" {{ $attributes->merge(['class' => "btn $variantClass", 'style' => $fullWidth ? 'width: 100%;' :
    '']) }}>
    {{ $slot }}
</button>
