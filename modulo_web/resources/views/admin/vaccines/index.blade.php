@extends('layouts.app')

@section('content')
<x-page-header title="Histórico de Vacinação"/>

<x-card>
    <div class="index-toolbar">
        <form method="GET" action="{{ route('admin.vaccines.index') }}" class="search-form">
            <select name="col" class="col-select">
                <option value="" {{ request(
                'col') === '' ? 'selected' : '' }}>Todos os campos</option>
                <option value="vaccine_type" {{ request(
                'col') === 'vaccine_type' ? 'selected' : '' }}>Tipo de Vacina</option>
                <option value="rfid_tag" {{ request(
                'col') === 'rfid_tag' ? 'selected' : '' }}>Tag RFID</option>
                <option value="animal" {{ request(
                'col') === 'animal' ? 'selected' : '' }}>Animal</option>
            </select>
            <input type="search" name="q" value="{{ request('q') }}"
                   placeholder="Pesquisar…"
                   class="recent-search-input">
            <button type="submit" class="search-btn">Buscar</button>
            @if(request('q'))
            <a href="{{ route('admin.vaccines.index') }}" class="search-clear">✕ Limpar</a>
            @endif
        </form>
        <span class="result-count">{{ $vaccines->total() }} registro(s)</span>
    </div>

    <x-table :headers="['Data', 'Animal (Tag)', 'Vacina', 'Peso na Aplicação', 'Veterinário']">
        @foreach($vaccines as $v)
        <tr data-testid="vaccine-row">
            <td>{{ \Carbon\Carbon::parse($v->vaccination_date)->format('d/m/Y') }}</td>
            <td>{{ $v->animal_name ?? 'Desconhecido' }} (<span
                    data-testid="vaccine-animal-tag"><code>{{ $v->rfid_tag }}</code></span>)
            </td>
            <td data-testid="vaccine-type">{{ $v->vaccine_type }}</td>
            <td>{{ number_format($v->current_weight, 2, ',', '.') }} kg</td>
            <td>{{ $v->veterinarian_name ?? 'Sistema' }}</td>
        </tr>
        @endforeach
        @if($vaccines->isEmpty())
        <tr>
            <td colspan="5" style="text-align: center; color: var(--secondary);">Nenhum registro encontrado.</td>
        </tr>
        @endif
    </x-table>

    @if($vaccines->hasPages())
    <div class="pagination-footer">
                <span class="result-count">
                    Exibindo {{ $vaccines->firstItem() }}–{{ $vaccines->lastItem() }} de {{ $vaccines->total() }}
                </span>
        {{ $vaccines->links() }}
    </div>
    @endif
</x-card>
@endsection
