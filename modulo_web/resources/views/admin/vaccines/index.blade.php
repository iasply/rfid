@extends('layouts.app')

@section('content')
    <x-page-header title="Histórico de Vacinação"/>

    <x-card>
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
                    <td colspan="5" style="text-align: center; color: var(--secondary);">Nenhum registro de vacinação.
                    </td>
                </tr>
            @endif
        </x-table>
    </x-card>
@endsection
