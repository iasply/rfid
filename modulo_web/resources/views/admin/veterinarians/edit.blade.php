@extends('layouts.app')

@section('content')
    <x-page-header title="Editar Veterinário" :backLink="route('admin.veterinarians.index')"/>

    <x-card maxWidth="600px">
        <form action="{{ route('admin.veterinarians.update', $veterinarian->id) }}" method="POST">
            @csrf
            @method('PUT')

            <x-input label="Nome Completo" name="name" :value="old('name', $veterinarian->name)" required/>

            <x-input label="Username (RFID/Identificador)" name="vet_rfid" :value="$veterinarian->vet_rfid" readonly/>

            <x-input label="E-mail" name="email" type="email" :value="old('email', $veterinarian->email)" required/>

            <x-input label="Nova Senha (deixe em branco para não alterar)" name="password" type="password"
                     showToggle="true"/>

            <x-button type="submit" fullWidth style="margin-top: 1rem;" data-testid="vet-submit-button">
                Salvar Alterações
            </x-button>
        </form>
    </x-card>
@endsection
