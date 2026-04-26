@props(['label', 'name', 'type' => 'text', 'value' => '', 'required' => false, 'readonly' => false])

<div style="margin-bottom: 1.5rem;">
    @if($label)
    <label for="{{ $name }}"
           style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem; color: var(--text-muted);">{{ $label
        }}</label>
    @endif

    <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}" value="{{ old($name, $value) }}"
           {{ $required ? 'required' : '' }} {{ $readonly ? 'readonly' : '' }}
    {{ $attributes->merge(['class' => 'form-control', 'data-testid' => $attributes->get('data-testid') ?? $name]) }}
    style="
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #e2e8f0;
    border-radius: var(--radius-md);
    transition: all 0.2s;
    font-size: 1rem;
    {{ $readonly ? 'background-color: var(--bg-main);' : '' }}
    ">

    @error($name)
    <span style="color: var(--danger); font-size: 0.8125rem; margin-top: 0.25rem; display: block;">{{ $message }}</span>
    @enderror
</div>

<style>
    input:focus {
        outline: none;
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    }
</style>
