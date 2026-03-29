@extends('layouts.app')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 style="margin: 0;">Resumo do Sistema</h2>
        <span style="color: var(--secondary);">Bem-vindo, {{ Auth::user()->name }}</span>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
        <div class="card" style="border-left: 5px solid var(--primary);">
            <h3 style="color: var(--secondary); font-size: 0.875rem; text-transform: uppercase;">Veterinários</h3>
            <p style="font-size: 2.5rem; font-weight: 800; margin: 0.5rem 0;"
               data-testid="dashboard-vets-count">{{ $stats['vets'] }}</p>
            <a href="{{ route('admin.veterinarians.index') }}"
               style="color: var(--primary); font-size: 0.875rem; text-decoration: none; font-weight: 600;">Ver todos
                →</a>
        </div>

        <div class="card" style="border-left: 5px solid var(--success);">
            <h3 style="color: var(--secondary); font-size: 0.875rem; text-transform: uppercase;">Animais (Gado)</h3>
            <p style="font-size: 2.5rem; font-weight: 800; margin: 0.5rem 0;"
               data-testid="dashboard-cattle-count">{{ $stats['cattle'] }}</p>
            <a href="{{ route('admin.cattle.index') }}"
               style="color: var(--primary); font-size: 0.875rem; text-decoration: none; font-weight: 600;">Ver todos
                →</a>
        </div>

        <div class="card" style="border-left: 5px solid orange;">
            <h3 style="color: var(--secondary); font-size: 0.875rem; text-transform: uppercase;">Vacinas Aplicadas</h3>
            <p style="font-size: 2.5rem; font-weight: 800; margin: 0.5rem 0;"
               data-testid="dashboard-vaccines-count">{{ $stats['vaccines'] }}</p>
            <a href="{{ route('admin.vaccines.index') }}"
               style="color: var(--primary); font-size: 0.875rem; text-decoration: none; font-weight: 600;">Histórico
                →</a>
        </div>
    </div>

    <div class="card" style="margin-top: 2rem;">
        <h3>Ações Rápidas</h3>
        <div style="display: flex; gap: 1rem;">
            <a href="{{ route('admin.veterinarians.create') }}" class="btn btn-primary" style="text-decoration: none;"
               data-testid="dashboard-new-vet-link">+
                Novo Veterinário</a>
            <a href="{{ route('admin.cattle.create') }}" class="btn btn-success" style="text-decoration: none;"
               data-testid="dashboard-new-cattle-link">+ Novo
                Animal</a>
        </div>
    </div>
@endsection
