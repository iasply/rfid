@props(['label', 'name', 'type' => 'text', 'value' => '', 'required' => false, 'readonly' => false, 'showToggle' => false])

<div style="margin-bottom: 1.5rem;">
    @if($label)
    <label for="{{ $name }}"
           style="display: block; margin-bottom: 0.5rem; font-size: 0.875rem; color: var(--text-muted);">{{ $label
        }}</label>
    @endif

    <div style="{{ $showToggle ? 'position: relative;' : '' }}">
        <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}" value="{{ old($name, $value) }}"
               {{ $required ? 'required' : '' }} {{ $readonly ? 'readonly' : '' }}
        {{ $attributes->merge(['class' => 'form-control', 'data-testid' => $attributes->get('data-testid') ?? $name]) }}
        style="
        width: 100%;
        padding: 0.75rem {{ $showToggle ? '3rem' : '1rem' }} 0.75rem 1rem;
        border: 1px solid #e2e8f0;
        border-radius: var(--radius-md);
        transition: all 0.2s;
        font-size: 1rem;
        {{ $readonly ? 'background-color: var(--bg-main);' : '' }}
        ">

        @if($showToggle)
        <button type="button"
                onclick="(function(btn){var inp=btn.previousElementSibling;var h=inp.type==='password';inp.type=h?'text':'password';btn.querySelector('.pw-eye-show').style.display=h?'none':'';btn.querySelector('.pw-eye-hide').style.display=h?'':'none';})(this)"
                aria-label="Mostrar/ocultar senha"
                style="position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);padding:0.25rem;display:flex;align-items:center;line-height:0;">
            <svg class="pw-eye-show" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
            </svg>
            <svg class="pw-eye-hide" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>
            </svg>
        </button>
        @endif
    </div>

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
