@extends('layouts.app')

@section('content')
    <x-page-header title="Rebanho Cadastrado">
        <x-slot name="actions">
            <a href="{{ route('admin.cattle.create') }}" class="btn btn-success" data-testid="create-cattle-link">+ Novo Animal</a>
        </x-slot>
    </x-page-header>

    <x-card>
        <div class="index-toolbar">
            <form method="GET" action="{{ route('admin.cattle.index') }}" class="search-form">
                <select name="col" class="col-select">
                    <option value=""       {{ request('col') === ''        ? 'selected' : '' }}>Todos os campos</option>
                    <option value="name"   {{ request('col') === 'name'    ? 'selected' : '' }}>Nome</option>
                    <option value="rfid_tag" {{ request('col') === 'rfid_tag' ? 'selected' : '' }}>Tag RFID</option>
                </select>
                <input type="search" name="q" value="{{ request('q') }}"
                       placeholder="Pesquisar…"
                       class="recent-search-input">
                <button type="submit" class="search-btn">Buscar</button>
                @if(request('q'))
                    <a href="{{ route('admin.cattle.index') }}" class="search-clear">✕ Limpar</a>
                @endif
            </form>
            <span class="result-count">{{ $gattos->total() }} animal(is)</span>
        </div>

        <x-table :headers="['Tag RFID', 'Nome/Apelido', 'Cadastrado por', 'Peso Atual', 'Data Registro', 'Ações']">
            @foreach($gattos as $animal)
                <tr data-testid="cattle-row">
                    <td data-testid="cattle-tag"><code>{{ $animal->rfid_tag }}</code></td>
                    <td data-testid="cattle-name">{{ $animal->name }}</td>
                    <td>{{ $animal->user->name ?? 'Sistema' }}</td>
                    <td class="text-right">{{ number_format($animal->weight, 2, ',', '.') }} kg</td>
                    <td>{{ \Carbon\Carbon::parse($animal->registration_date)->format('d/m/Y') }}</td>
                    <td class="text-right" style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                        <a href="{{ route('admin.cattle.show', $animal->id) }}" class="btn btn-primary"
                           style="font-size: 0.75rem; text-decoration: none; padding: 0.4rem 0.8rem;"
                           data-testid="cattle-show-link">Ver</a>
                        <a href="{{ route('admin.cattle.edit', $animal->id) }}" class="btn btn-primary"
                           style="font-size: 0.75rem; text-decoration: none; padding: 0.4rem 0.8rem;"
                           data-testid="cattle-edit-link">Editar</a>
                    </td>
                </tr>
            @endforeach
            @if($gattos->isEmpty())
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--secondary);">Nenhum animal encontrado.</td>
                </tr>
            @endif
        </x-table>

        @if($gattos->hasPages())
            <div class="pagination-footer">
                <span class="result-count">
                    Exibindo {{ $gattos->firstItem() }}–{{ $gattos->lastItem() }} de {{ $gattos->total() }}
                </span>
                {{ $gattos->links() }}
            </div>
        @endif
    </x-card>
@endsection
