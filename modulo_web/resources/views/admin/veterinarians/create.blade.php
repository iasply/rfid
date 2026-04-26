@extends('layouts.app')

@section('content')
<x-page-header title="Novo Veterinário" :backLink="route('admin.veterinarians.index')"/>

<x-card maxWidth="600px">
    <form action="{{ route('admin.veterinarians.store') }}" method="POST">
        @csrf

        <x-input label="Nome Completo" name="name" required placeholder="Ex: Dr. João Silva"/>

        <x-input label="E-mail" name="email" type="email" required placeholder="joao@exemplo.com"/>

        <x-input label="Senha de Acesso" name="password" type="password" required/>

        <x-button type="submit" fullWidth style="margin-top: 1rem;" data-testid="vet-submit-button">
            Cadastrar Veterinário
        </x-button>
    </form>
</x-card>
@endsection
