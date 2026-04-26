@extends('layouts.app')

@section('content')
<script>
    window.__animalData = {
        weightOverTime: @json($chartWeightOverTime),
        vaccineTypes: @json($chartAnimalVaccineTypes),
    };
</script>
<x-page-header :title="'Animal: ' . $cattle->name" :backLink="route('admin.cattle.index')">
    <x-slot name="actions">
        <x-button variant="secondary" onclick="window.location='{{ route('admin.cattle.edit', $cattle->id) }}'">
            Editar Animal
        </x-button>
    </x-slot>
</x-page-header>

<div
    style="display: grid; grid-template-columns: 1fr; gap: 2rem; margin-bottom: 2rem; @media (min-width: 1024px) { grid-template-columns: 350px 1fr; }">
    <x-card>
        <h3 style="margin-bottom: 1.5rem; font-size: 1.125rem; font-weight: 700;">Detalhes do Animal</h3>

        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <div>
                    <span
                        style="display: block; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Tag
                        RFID</span>
                <code
                    style="font-size: 1.125rem; color: var(--primary-dark); font-weight: 700;">{{ $cattle->rfid_tag
                    }}</code>
            </div>

            <div>
                    <span
                        style="display: block; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Nome
                        / Apelido</span>
                <span style="font-size: 1rem; font-weight: 500;">{{ $cattle->name }}</span>
            </div>

            <div
                style="display: flex; justify-content: space-between; align-items: center; background: var(--bg-main); padding: 1rem; border-radius: var(--radius-md);">
                <div>
                        <span
                            style="display: block; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Peso
                            Atual</span>
                    <span
                        style="font-size: 1.25rem; font-weight: 800; color: var(--primary-dark);">{{ number_format($cattle->weight, 2, ',', '.') }}
                            kg</span>
                </div>
            </div>

            <div>
                    <span
                        style="display: block; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Data
                        de Registro</span>
                <span
                    style="font-size: 0.9375rem;">{{ \Carbon\Carbon::parse($cattle->registration_date)->format('d/m/Y') }}</span>
            </div>

            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--bg-main);">
                    <span
                        style="display: block; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700;">Cadastrado
                        por</span>
                <span style="font-size: 0.9375rem; font-weight: 500;">{{ $cattle->user->name ?? 'Sistema' }}</span>
            </div>
        </div>
    </x-card>

    <x-card>
        <h3 style="margin-bottom: 1.5rem; font-size: 1.125rem; font-weight: 700;">Histórico de Vacinação</h3>

        <x-table :headers="['Data', 'Vacina', 'Peso na Época', 'Veterinário']">
            @foreach($vaccines as $v)
            <tr>
                <td>{{ \Carbon\Carbon::parse($v->vaccination_date)->format('d/m/Y') }}</td>
                <td>
                    <span style="font-weight: 600; color: var(--primary-dark);">{{ $v->vaccine_type }}</span>
                </td>
                <td class="text-right">{{ number_format($v->current_weight, 2, ',', '.') }} kg</td>
                <td>{{ $v->veterinarian_name ?? 'Sistema' }}</td>
            </tr>
            @endforeach
            @if($vaccines->isEmpty())
            <tr>
                <td colspan="4" style="text-align: center; color: var(--secondary); padding: 2rem;">Nenhuma
                    vacina
                    registrada para este animal.
                </td>
            </tr>
            @endif
        </x-table>
    </x-card>
</div>

<div class="chart-grid">
    <div class="card chart-card">
        <p class="chart-title">Evolução de Peso
            <span class="chart-subtitle">(kg por evento de vacinação)</span>
        </p>
        <div class="chart-wrap">
            <canvas id="chart-animal-weight"></canvas>
        </div>
    </div>

    <div class="card chart-card">
        <p class="chart-title">Vacinas Recebidas</p>
        <div class="chart-wrap">
            <canvas id="chart-animal-vaccines"></canvas>
        </div>
    </div>
</div>
@endsection
