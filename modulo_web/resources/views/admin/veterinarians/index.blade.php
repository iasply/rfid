@extends('layouts.app')

@section('content')
    <x-page-header title="Veterinários Cadastrados">
        <x-slot name="actions">
            <x-button onclick="window.location='{{ route('admin.veterinarians.create') }}'"
                      data-testid="create-vet-button">
                + Novo Veterinário
            </x-button>
        </x-slot>
    </x-page-header>

    <x-card>
        <div class="index-toolbar">
            <form method="GET" action="{{ route('admin.veterinarians.index') }}" class="search-form">
                <select name="col" class="col-select">
                    <option value=""      {{ request('col') === ''      ? 'selected' : '' }}>Todos os campos</option>
                    <option value="name"  {{ request('col') === 'name'  ? 'selected' : '' }}>Nome</option>
                    <option value="email" {{ request('col') === 'email' ? 'selected' : '' }}>E-mail</option>
                </select>
                <input type="search" name="q" value="{{ request('q') }}"
                       placeholder="Pesquisar…"
                       class="recent-search-input">
                <button type="submit" class="search-btn">Buscar</button>
                @if(request('q'))
                    <a href="{{ route('admin.veterinarians.index') }}" class="search-clear">✕ Limpar</a>
                @endif
            </form>
            <span class="result-count">{{ $vets->total() }} veterinário(s)</span>
        </div>

        <x-table :headers="['Nome', 'Tag Rfid', 'Email', 'Ações']">
            @foreach($vets as $vet)
                <tr data-testid="vet-row">
                    <td data-testid="vet-name">{{ $vet->name }}</td>
                    <td data-testid="vet-rfid"><code>{{ $vet->vet_rfid }}</code></td>
                    <td>{{ $vet->email }}</td>
                    <td class="text-right" style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                        <a href="{{ route('admin.veterinarians.show', $vet->id) }}" class="btn btn-primary"
                           style="font-size: 0.75rem; text-decoration: none; padding: 0.4rem 0.8rem;"
                           data-testid="vet-show-link">Ver</a>
                        <a href="{{ route('admin.veterinarians.edit', $vet->id) }}" class="btn btn-primary"
                           style="font-size: 0.75rem; text-decoration: none; padding: 0.4rem 0.8rem;"
                           data-testid="vet-edit-link">Editar</a>
                    </td>
                </tr>
            @endforeach
            @if($vets->isEmpty())
                <tr>
                    <td colspan="4" style="text-align: center; color: var(--secondary);">Nenhum veterinário encontrado.</td>
                </tr>
            @endif
        </x-table>

        @if($vets->hasPages())
            <div class="pagination-footer">
                <span class="result-count">
                    Exibindo {{ $vets->firstItem() }}–{{ $vets->lastItem() }} de {{ $vets->total() }}
                </span>
                {{ $vets->links() }}
            </div>
        @endif
    </x-card>
@endsection
