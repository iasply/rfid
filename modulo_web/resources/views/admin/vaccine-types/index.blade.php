@extends('layouts.app')

@section('content')
<x-page-header title="Tipos de Vacina">
    <x-slot name="actions">
        <a href="{{ route('admin.vaccine-types.create') }}" class="btn btn-primary">
            + Novo Tipo
        </a>
    </x-slot>
</x-page-header>

<x-card>
    <x-table :headers="['Nome', 'Intervalo', 'Meses de Pico', 'Descrição', '']">
        @forelse($vaccineTypes as $vt)
            <tr>
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
                <td>
                    <a href="{{ route('admin.vaccine-types.edit', $vt->id) }}"
                       style="font-size: 0.8rem; font-weight: 600; color: var(--primary); text-decoration: none; white-space: nowrap;">
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
