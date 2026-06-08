@extends('layouts.app')

@section('content')
    <x-page-header title="Novo Tipo de Vacina" :backLink="route('admin.vaccine-types.index')" backText="Voltar"/>

    <x-card maxWidth="680px">
        <form action="{{ route('admin.vaccine-types.store') }}" method="POST">
            @csrf

            <x-input label="Nome da Vacina" name="name" required :value="old('name')" placeholder="Ex: Febre Aftosa"/>

            <div style="margin-bottom: 1rem;">
                <label
                    style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem;">Descrição</label>
                <textarea name="description" rows="3"
                          style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid rgba(226,232,240,0.9); border-radius: var(--radius-md); font-family: inherit; font-size: 0.875rem; resize: vertical;"
                          placeholder="Contexto clínico, protocolo e observações…">{{ old('description') }}</textarea>
                @error('description') <span
                    style="color: var(--danger); font-size: 0.8rem;">{{ $message }}</span> @enderror
            </div>

            <x-input label="Intervalo para próxima dose (dias)" name="interval_days" type="number" min="1" max="3650"
                     :value="old('interval_days')" placeholder="Ex: 180"/>
            <p style="margin-top: -0.75rem; margin-bottom: 1rem; font-size: 0.78rem; color: var(--text-muted);">
                Deixe em branco se a vacina não tem calendário de revacinação.
            </p>

            {{-- Season months checkboxes --}}
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.75rem;">
                    Meses de aplicação (época)
                </label>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 0.5rem;">
                    @foreach($monthNames as $num => $label)
                        <label
                            style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.85rem; cursor: pointer;">
                            <input type="checkbox" name="season_months[]" value="{{ $num }}"
                                {{ in_array($num, old('season_months', [])) ? 'checked' : '' }}>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
                <p style="margin-top: 0.5rem; font-size: 0.78rem; color: var(--text-muted);">
                    Selecione os meses em que a campanha de vacinação é recomendada. Deixe vazio para o ano todo.
                </p>
            </div>

            <x-button type="submit" fullWidth data-testid="vaccine-type-submit" style="margin-top: 0.5rem;">Cadastrar
                Tipo
                de Vacina
            </x-button>
        </form>
    </x-card>
@endsection
