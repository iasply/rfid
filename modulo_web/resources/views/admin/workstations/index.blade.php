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
                    <td colspan="3" style="text-align: center; color: var(--secondary);">Nenhuma estação cadastrada.
                    </td>
                </tr>
            @endif
        </x-table>
    </x-card>
@endsection
