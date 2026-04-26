@props(['width' => '100%', 'maxWidth' => 'none', 'glass' => false])

<div {{ $attributes->merge(['class' => 'card ' . ($glass ? 'glass' : ''), 'style' => "width: $width; max-width:
    $maxWidth;"]) }}>
    {{ $slot }}
</div>

<style>
    .card.glass {
        background: var(--glass);
        backdrop-filter: blur(12px);
        border: 1px solid var(--glass-border);
    }
</style>
