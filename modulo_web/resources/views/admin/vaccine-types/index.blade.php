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
        <div class="index-toolbar">
            <form method="GET" action="{{ route('admin.vaccine-types.index') }}" class="search-form">
                <select name="col" class="col-select">
                    <option value="" {{ request('col') === '' ? 'selected' : '' }}>Todos os campos</option>
                    <option value="name" {{ request('col') === 'name' ? 'selected' : '' }}>Nome</option>
                    <option value="description" {{ request('col') === 'description' ? 'selected' : '' }}>Descrição
                    </option>
                </select>
                <input type="search" name="q" value="{{ request('q') }}"
                       placeholder="Pesquisar…"
                       class="recent-search-input">
                <button type="submit" class="search-btn">Buscar</button>
                @if(request('q'))
                    <a href="{{ route('admin.vaccine-types.index') }}" class="search-clear">✕ Limpar</a>
                @endif
            </form>
            <span class="result-count">{{ $vaccineTypes->total() }} tipo(s)</span>
        </div>

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
                    <td style="max-width: 360px; overflow: hidden; text-overflow: ellipsis; font-size: 0.82rem; color: var(--text-muted);">
                        {{ Str::limit($vt->description, 100) }}
                    </td>
                    <td class="text-right" style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                        <a href="{{ route('admin.vaccine-types.show', $vt->id) }}"
                           class="btn btn-primary"
                           style="font-size: 0.75rem; text-decoration: none; padding: 0.4rem 0.8rem;"
                           data-testid="vaccine-type-show-link">Ver</a>
                        <a href="{{ route('admin.vaccine-types.edit', $vt->id) }}"
                           class="btn btn-primary"
                           style="font-size: 0.75rem; text-decoration: none; padding: 0.4rem 0.8rem;"
                           data-testid="vaccine-type-edit-link">Editar</a>
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
