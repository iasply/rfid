@props(['title', 'backLink' => null, 'backText' => 'Voltar'])

<div style="margin-bottom: 2.5rem;">
    @if($backLink)
    <a href="{{ $backLink }}"
       style="display: inline-flex; align-items: center; color: var(--text-muted); text-decoration: none; font-size: 0.875rem; margin-bottom: 0.75rem; transition: color 0.2s;"
       onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--text-muted)'">
        <span style="margin-right: 0.5rem;">←</span> {{ $backText }}
    </a>
    @endif

    <div
        style="display: flex; flex-direction: column; gap: 1rem; @media (min-width: 640px) { flex-direction: row; justify-content: space-between; align-items: center; }">
        <h2
            style="font-size: 1.875rem; font-weight: 800; letter-spacing: -0.025em; color: var(--text-main); margin: 0;">
            {{ $title }}
        </h2>
        @if(isset($actions))
        <div style="display: flex; gap: 0.75rem; align-items: center;">
            {{ $actions }}
        </div>
        @endif
    </div>
</div>
