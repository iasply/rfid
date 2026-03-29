@extends('layouts.app')

@section('content')
    <x-page-header title="Rebanho Cadastrado">
        <x-slot name="actions">
            <a href="{{ route('admin.cattle.create') }}" class="btn btn-success" data-testid="create-cattle-link">+ Novo
                Animal</a>
        </x-slot>
    </x-page-header>

    <x-card>
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
                    <td colspan="6" style="text-align: center; color: var(--secondary);">Nenhum animal cadastrado.</td>
                </tr>
            @endif
        </x-table>
    </x-card>
@endsection
