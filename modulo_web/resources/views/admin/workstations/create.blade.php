@extends('layouts.app')

@section('content')
    <x-page-header title="Nova Estação de Trabalho" :backLink="route('admin.workstations.index')"/>

    <x-card maxWidth="600px">
        <div style="margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--bg-main);">
            <p style="color: var(--text-muted); font-size: 0.875rem;">
                As estações de trabalho são identificadas automaticamente por um <strong>Hash Único</strong> gerado pelo
                sistema no momento da criação.
            </p>
        </div>

        <form action="{{ route('admin.workstations.store') }}" method="POST">
            @csrf

            <x-input label="Descrição / Localização da Estação" name="desc" required
                     placeholder="Ex: Curral Principal, Laboratório A..."/>

            <div style="margin-top: 2rem;">
                <x-button type="submit" fullWidth data-testid="workstation-submit-button">
                    Cadastrar Estação
                </x-button>
            </div>
        </form>
    </x-card>
@endsection
