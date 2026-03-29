@extends('layouts.app')

@section('content')
    <x-page-header title="Cadastrar Novo Animal" :backLink="route('admin.cattle.index')" backText="Voltar para Lista"/>

    <x-card maxWidth="600px">
        <form action="{{ route('admin.cattle.store') }}" method="POST">
            @csrf

            <x-input label="Nome / Apelido" name="name" required placeholder="Ex: Mimosa"/>

            <x-input label="Peso Inicial (kg)" name="weight" type="number" step="0.01" required/>

            <x-button type="submit" variant="success" fullWidth style="margin-top: 1rem;"
                      data-testid="cattle-submit-button">
                Finalizar Cadastro
            </x-button>
        </form>
    </x-card>
@endsection
