@extends('layouts.app')

@section('content')
<x-page-header title="Avisos de Vacinação">
    <x-slot name="actions">
        <span style="font-size: 0.85rem; color: var(--text-muted);">Janela de alerta: próximos 30 dias</span>
    </x-slot>
</x-page-header>

{{-- KPI Summary --}}
<div class="kpi-grid" style="margin-bottom: 1.5rem;">
    <div class="card kpi-card kpi-danger">
        <p class="kpi-label">Atrasadas</p>
        <p class="kpi-value" style="color: var(--danger);">{{ $totalOverdue }}</p>
        <span class="kpi-sub">Intervalo já ultrapassado</span>
    </div>
    <div class="card kpi-card kpi-warning">
        <p class="kpi-label">Vence em 30 dias</p>
        <p class="kpi-value" style="color: var(--accent);">{{ $totalDueSoon }}</p>
        <span class="kpi-sub">Dentro da janela de alerta</span>
    </div>
    <div class="card kpi-card kpi-secondary">
        <p class="kpi-label">Nunca Vacinado</p>
        <p class="kpi-value" style="color: var(--secondary);">{{ $totalNever }}</p>
        <span class="kpi-sub">Vacinas em época — sem registro</span>
    </div>
</div>

{{-- Type filter --}}
<div class="index-toolbar" style="margin-bottom: 1.5rem;">
    <form method="GET" action="{{ route('admin.alerts') }}" class="search-form">
        <select name="type" class="col-select" onchange="this.form.submit()">
            <option value="">Todos os tipos de vacina</option>
            @foreach($vaccineTypes as $type)
                <option value="{{ $type }}" {{ $typeFilter === $type ? 'selected' : '' }}>{{ $type }}</option>
            @endforeach
        </select>
        @if($typeFilter)
            <a href="{{ route('admin.alerts') }}" class="search-clear">✕ Limpar filtro</a>
        @endif
    </form>
    <span class="result-count">
        {{ collect($alertsByType)->sum('total') }} animal(is) com pendências
    </span>
</div>

{{-- Alert sections --}}
@forelse($alertsByType as $vaccineType => $group)
    <div class="card" style="margin-bottom: 1.25rem;">

        {{-- Section header --}}
        <div style="padding-bottom: 1rem; margin-bottom: 1rem; border-bottom: 1px solid rgba(226,232,240,0.6);">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
                <div>
                    <h3 style="margin: 0 0 0.35rem; font-size: 1.05rem; font-weight: 800; color: var(--text-main);">
                        {{ $vaccineType }}
                        @if($group['in_season'])
                            <span style="font-size: 0.7rem; font-weight: 700; background: rgba(16,185,129,0.12); color: var(--primary-dark); padding: 0.15rem 0.55rem; border-radius: 999px; vertical-align: middle; margin-left: 0.4rem;">em época</span>
                        @endif
                    </h3>
                    <p style="margin: 0 0 0.5rem; font-size: 0.8rem; color: var(--text-muted); max-width: 640px; line-height: 1.5;">
                        {{ $group['description'] }}
                    </p>
                    <span style="font-size: 0.75rem; color: var(--secondary); font-weight: 600;">
                        Intervalo recomendado: {{ $group['interval'] }} dias
                        &nbsp;·&nbsp;
                        {{ $group['total'] }} animal(is) pendente(s)
                    </span>
                </div>
                <div style="display: flex; gap: 0.4rem; flex-wrap: wrap; flex-shrink: 0;">
                    @php
                        $nOverdue = $group['rows']->where('urgency', 'overdue')->count();
                        $nSoon    = $group['rows']->where('urgency', 'due_soon')->count();
                        $nNever   = $group['rows']->where('urgency', 'never')->count();
                        // totals across all rows (not just displayed page)
                        $tOverdue = $group['total'] - collect($group['rows'])->where('urgency', 'due_soon')->count() - collect($group['rows'])->where('urgency', 'never')->count();
                    @endphp
                    @if($group['rows']->where('urgency', 'overdue')->count() || (!$typeFilter && $group['total'] > 0))
                        <span class="alert-badge alert-badge--overdue">
                            {{ $group['rows']->where('urgency', 'overdue')->count() }} atrasada(s)
                        </span>
                    @endif
                    @if($group['rows']->where('urgency', 'due_soon')->count())
                        <span class="alert-badge alert-badge--soon">
                            {{ $group['rows']->where('urgency', 'due_soon')->count() }} vence em breve
                        </span>
                    @endif
                    @if($group['rows']->where('urgency', 'never')->count())
                        <span class="alert-badge alert-badge--never">
                            {{ $group['rows']->where('urgency', 'never')->count() }} nunca vacinado(s)
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-wrap">
            <table class="activity-table">
                <thead>
                    <tr>
                        <th>Animal</th>
                        <th>Tag RFID</th>
                        <th>Última Vacinação</th>
                        <th>Próxima Dose</th>
                        <th>Situação</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($group['rows'] as $row)
                        <tr style="{{ $row['urgency'] === 'overdue' ? 'background: rgba(239,68,68,0.03);' : '' }}">
                            <td><strong>{{ $row['name'] }}</strong></td>
                            <td><code>{{ $row['rfid_tag'] }}</code></td>
                            <td>
                                @if($row['last_vax'])
                                    {{ \Carbon\Carbon::parse($row['last_vax'])->format('d/m/Y') }}
                                    <span style="display: block; font-size: 0.74rem; color: var(--text-muted);">
                                        há {{ $row['days_since'] }} dias
                                    </span>
                                @else
                                    <span style="color: var(--text-muted);">—</span>
                                @endif
                            </td>
                            <td>
                                @if($row['next_due'])
                                    {{ $row['next_due'] }}
                                    @if($row['days_remaining'] <= 0)
                                        <span style="display: block; font-size: 0.74rem; color: var(--danger); font-weight: 700;">
                                            {{ abs($row['days_remaining']) }}d em atraso
                                        </span>
                                    @else
                                        <span style="display: block; font-size: 0.74rem; color: #92400e; font-weight: 600;">
                                            em {{ $row['days_remaining'] }} dia(s)
                                        </span>
                                    @endif
                                @else
                                    <span style="color: var(--text-muted);">Nunca aplicado</span>
                                @endif
                            </td>
                            <td>
                                @if($row['urgency'] === 'overdue')
                                    <span class="alert-badge alert-badge--overdue">Atrasada</span>
                                @elseif($row['urgency'] === 'due_soon')
                                    <span class="alert-badge alert-badge--soon">Vence em breve</span>
                                @else
                                    <span class="alert-badge alert-badge--never">Nunca vacinado</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.cattle.show', $row['id']) }}"
                                   style="font-size: 0.8rem; font-weight: 600; color: var(--primary); text-decoration: none; white-space: nowrap;">
                                    Ver animal →
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination (single type selected) --}}
        @if($typeFilter && $group['paginator'] && $group['paginator']->hasPages())
            <div class="pagination-footer">
                <span class="result-count">
                    Exibindo {{ $group['paginator']->firstItem() }}–{{ $group['paginator']->lastItem() }}
                    de {{ $group['paginator']->total() }}
                </span>
                {{ $group['paginator']->links() }}
            </div>

        {{-- "See all" link (all types mode, results truncated) --}}
        @elseif(!$typeFilter && $group['has_more'])
            <div style="margin-top: 1rem; text-align: center;">
                <a href="{{ route('admin.alerts', ['type' => $vaccineType]) }}"
                   style="font-size: 0.85rem; font-weight: 600; color: var(--primary); text-decoration: none;">
                    Ver todos os {{ $group['total'] }} animais pendentes para {{ $vaccineType }} →
                </a>
            </div>
        @endif
    </div>
@empty
    <x-card>
        <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
            <div style="font-size: 3rem; margin-bottom: 1rem;">✅</div>
            <h3 style="margin: 0 0 0.5rem; font-size: 1.125rem; color: var(--text-main);">Rebanho em dia!</h3>
            <p style="margin: 0; font-size: 0.9rem;">Nenhuma vacina pendente ou vencendo nos próximos 30 dias.</p>
        </div>
    </x-card>
@endforelse

@endsection
