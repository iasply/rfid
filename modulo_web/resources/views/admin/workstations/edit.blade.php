@extends('layouts.app')

@section('content')
    <x-page-header title="Editar Estação de Trabalho" :backLink="route('admin.workstations.index')"/>

    <x-card maxWidth="600px">
        <form action="{{ route('admin.workstations.update', $workstation->id) }}" method="POST">
            @csrf
            @method('PUT')

            <x-input label="Hash (Imutável)" name="hash" :value="$workstation->hash" readonly/>

            <x-input label="Descrição / Localização" name="desc" :value="old('desc', $workstation->desc)" required
                     placeholder="Ex: Computador da Recepção"/>

            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <x-button type="submit">Salvar Alterações</x-button>
                <x-button variant="secondary" onclick="window.location='{{ route('admin.workstations.index') }}'">
                    Cancelar
                </x-button>
            </div>
        </form>
    </x-card>
@endsection
