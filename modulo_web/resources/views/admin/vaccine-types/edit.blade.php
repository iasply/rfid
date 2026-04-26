@extends('layouts.app')

@section('content')
<x-page-header title="Editar: {{ $vaccineType->name }}" :backLink="route('admin.vaccine-types.index')" backText="Voltar"/>

<x-card maxWidth="680px">
    <form action="{{ route('admin.vaccine-types.update', $vaccineType->id) }}" method="POST">
        @csrf
        @method('PUT')

        <x-input label="Nome da Vacina" name="name" required :value="old('name', $vaccineType->name)"/>

        <div style="margin-bottom: 1rem;">
            <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Descrição</label>
            <textarea name="description" rows="3"
                style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(226,232,240,0.9); border-radius: var(--radius-md); font-family: inherit; font-size: 0.875rem; resize: vertical;">{{ old('description', $vaccineType->description) }}</textarea>
            @error('description') <span style="color: var(--danger); font-size: 0.8rem;">{{ $message }}</span> @enderror
        </div>

        <x-input label="Intervalo para próxima dose (dias)" name="interval_days" type="number" min="1" max="3650"
                 :value="old('interval_days', $vaccineType->interval_days)"/>
        <p style="margin-top: -0.75rem; margin-bottom: 1rem; font-size: 0.78rem; color: var(--text-muted);">
            Deixe em branco se a vacina não tem calendário de revacinação.
        </p>

        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.75rem;">
                Meses de aplicação (época)
            </label>
            @php
                $selectedMonths = old('season_months', $vaccineType->season_months ?? []);
            @endphp
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem;">
                @foreach($monthNames as $num => $label)
                    <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.85rem; cursor: pointer;">
                        <input type="checkbox" name="season_months[]" value="{{ $num }}"
                            {{ in_array($num, $selectedMonths) ? 'checked' : '' }}>
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </div>

        <x-button type="submit" fullWidth data-testid="vaccine-type-submit" style="margin-top: 0.5rem;">Salvar Alterações</x-button>
    </form>
</x-card>
@endsection
