@extends('layouts.app')

@section('content')
    <x-page-header :title="'Editar Animal: ' . $cattle->rfid_tag" :backLink="route('admin.cattle.index')"
                   backText="Voltar para Lista"/>

    <x-card maxWidth="600px">
        <form action="{{ route('admin.cattle.update', $cattle->id) }}" method="POST">
            @csrf
            @method('PUT')

            <x-input label="Tag RFID" name="rfid_tag" :value="$cattle->rfid_tag" readonly/>

            <x-input label="Nome / Apelido" name="name" :value="$cattle->name" required/>

            <x-input label="Peso (kg)" name="weight" type="number" step="0.01" :value="$cattle->weight" required/>

            <x-button type="submit" fullWidth style="margin-top: 1rem;" data-testid="cattle-submit-button">
                Salvar Alterações
            </x-button>
        </form>
    </x-card>
@endsection
