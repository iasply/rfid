@props(['title', 'backLink' => null, 'backText' => 'Voltar'])

<div class="page-header">
    @if($backLink)
    <a href="{{ $backLink }}" class="page-header__back">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
        {{ $backText }}
    </a>
    @endif

    <div class="page-header__inner">
        <h2 class="page-header__title">{{ $title }}</h2>
        @if(isset($actions))
        <div class="page-header__actions">
            {{ $actions }}
        </div>
        @endif
    </div>
</div>
