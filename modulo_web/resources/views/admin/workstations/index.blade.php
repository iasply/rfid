@extends('layouts.app')

@section('content')
    <x-page-header title="Estações de Trabalho">
        <x-slot name="actions">
            <x-button onclick="window.location='{{ route('admin.workstations.create') }}'"
                      data-testid="create-workstation-button">
                + Nova Estação
            </x-button>
        </x-slot>
    </x-page-header>

    <x-card>
        <div class="index-toolbar">
            <form method="GET" action="{{ route('admin.workstations.index') }}" class="search-form">
                <select name="col" class="col-select">
                    <option value=""     {{ request('col') === ''     ? 'selected' : '' }}>Todos os campos</option>
                    <option value="desc" {{ request('col') === 'desc' ? 'selected' : '' }}>Descrição</option>
                    <option value="hash" {{ request('col') === 'hash' ? 'selected' : '' }}>Hash</option>
                </select>
                <input type="search" name="q" value="{{ request('q') }}"
                       placeholder="Pesquisar…"
                       class="recent-search-input">
                <button type="submit" class="search-btn">Buscar</button>
                @if(request('q'))
                    <a href="{{ route('admin.workstations.index') }}" class="search-clear">✕ Limpar</a>
                @endif
            </form>
            <span class="result-count">{{ $workstations->total() }} estação(ões)</span>
        </div>

        <x-table :headers="['Hash', 'Descrição', 'Ações']">
            @foreach($workstations as $ws)
                <tr data-testid="workstation-row">
                    <td data-testid="workstation-hash"><code>{{ $ws->hash }}</code></td>
                    <td>{{ $ws->desc }}</td>
                    <td class="text-right" style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                        <a href="{{ route('admin.workstations.edit', $ws->id) }}" class="btn btn-primary"
                           style="font-size: 0.75rem; text-decoration: none; padding: 0.4rem 0.8rem;"
                           data-testid="workstation-edit-link">Editar</a>
                    </td>
                </tr>
            @endforeach
            @if($workstations->isEmpty())
                <tr>
                    <td colspan="3" style="text-align: center; color: var(--secondary);">Nenhuma estação encontrada.</td>
                </tr>
            @endif
        </x-table>

        @if($workstations->hasPages())
            <div class="pagination-footer">
                <span class="result-count">
                    Exibindo {{ $workstations->firstItem() }}–{{ $workstations->lastItem() }} de {{ $workstations->total() }}
                </span>
                {{ $workstations->links() }}
            </div>
        @endif
    </x-card>
@endsection
