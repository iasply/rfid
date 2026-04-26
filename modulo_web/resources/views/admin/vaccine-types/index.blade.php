@extends('layouts.app')

@section('content')
<x-page-header title="Tipos de Vacina">
    <x-slot name="actions">
        <a href="{{ route('admin.vaccine-types.create') }}" class="btn btn-primary"
           data-testid="create-vaccine-type-link">
            + Novo Tipo
        </a>
    </x-slot>
</x-page-header>

<x-card>
    <x-table :headers="['Nome', 'Intervalo', 'Meses de Pico', 'Descrição', 'Ações']">
        @forelse($vaccineTypes as $vt)
        <tr data-testid="vaccine-type-row">
            <td><strong>{{ $vt->name }}</strong></td>
            <td>
                @if($vt->interval_days)
                {{ $vt->interval_days }} dias
                @else
                <span style="color: var(--text-muted);">—</span>
                @endif
            </td>
            <td>
                @if($vt->season_months)
                @php
                $abbr = ['', 'Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
                @endphp
                <span style="font-size: 0.8rem; color: var(--text-muted);">
                            {{ implode(', ', array_map(fn($m) => $abbr[$m], $vt->season_months)) }}
                        </span>
                @else
                <span style="color: var(--text-muted);">Todos</span>
                @endif
            </td>
            <td style="max-width: 360px; font-size: 0.82rem; color: var(--text-muted);">
                {{ Str::limit($vt->description, 100) }}
            </td>
            <td style="white-space: nowrap; display: flex; gap: 1rem;">
                <a href="{{ route('admin.vaccine-types.show', $vt->id) }}"
                   data-testid="vaccine-type-show-link"
                   style="font-size: 0.8rem; font-weight: 600; color: var(--secondary); text-decoration: none;">
                    Ver →
                </a>
                <a href="{{ route('admin.vaccine-types.edit', $vt->id) }}"
                   data-testid="vaccine-type-edit-link"
                   style="font-size: 0.8rem; font-weight: 600; color: var(--primary); text-decoration: none;">
                    Editar →
                </a>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" style="text-align: center; color: var(--secondary); padding: 2rem;">
                Nenhum tipo de vacina cadastrado.
            </td>
        </tr>
        @endforelse
    </x-table>

    @if($vaccineTypes->hasPages())
    <div class="pagination-footer">
            <span class="result-count">
                Exibindo {{ $vaccineTypes->firstItem() }}–{{ $vaccineTypes->lastItem() }} de {{ $vaccineTypes->total() }}
            </span>
        {{ $vaccineTypes->links() }}
    </div>
    @endif
</x-card>
@endsection
