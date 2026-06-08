@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('content')
    <x-page-header title="{{ $vaccineType->name }}" :backLink="route('admin.vaccine-types.index')"
                   backText="← Tipos de Vacina">
        <x-slot name="actions">
            <a href="{{ route('admin.vaccine-types.edit', $vaccineType->id) }}"
               style="padding: 0.5rem 1.25rem; background: var(--primary); color: #fff; border-radius: var(--radius-md); text-decoration: none; font-size: 0.875rem; font-weight: 600;">
                Editar
            </a>
        </x-slot>
    </x-page-header>

    {{-- Info strip --}}
    <x-card style="margin-bottom: 1.5rem;">
        <div style="display: flex; flex-wrap: wrap; gap: 2rem; align-items: flex-start;">
            @if($vaccineType->description)
                <div style="flex: 1; min-width: 0;">
                    <p style="margin: 0; font-size: 0.9rem; color: var(--text-muted);">{{ $vaccineType->description }}</p>
                </div>
            @endif
            <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
                @if($vaccineType->interval_days)
                    <div>
                        <div
                            style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">
                            Intervalo
                        </div>
                        <div style="font-size: 1.1rem; font-weight: 700; color: var(--primary);">{{ $vaccineType->interval_days
                    }} dias
                        </div>
                    </div>
                @endif
                @if($vaccineType->season_months)
                    @php
                        $abbr = ['','Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
                    @endphp
                    <div style="max-width: 100%;">
                        <div
                            style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.35rem;">
                            Época
                        </div>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.25rem;">
                            @foreach($vaccineType->season_months as $m)
                                <span
                                    style="font-size: 0.75rem; font-weight: 600; background: rgba(16,185,129,0.1); color: var(--primary-dark); padding: 0.2rem 0.55rem; border-radius: 999px; white-space: nowrap;">{{ $abbr[$m] }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </x-card>

    {{-- Summary cards --}}
    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div
            style="background: #fff; border-radius: var(--radius-lg); padding: 1.25rem 1.5rem; border: 1px solid var(--border);">
            <div
                style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">
                Total Aplicações
            </div>
            <div style="font-size: 2rem; font-weight: 800; color: var(--primary);">{{ $totalApplications }}</div>
        </div>
        <div
            style="background: #fff; border-radius: var(--radius-lg); padding: 1.25rem 1.5rem; border: 1px solid var(--border);">
            <div
                style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">
                Bovinos Vacinados
            </div>
            <div style="font-size: 2rem; font-weight: 800; color: var(--primary);">{{ $vaccinatedCount }}</div>
            <div style="font-size: 0.78rem; color: var(--text-muted);">de {{ $totalCattle }} no rebanho</div>
        </div>
        <div
            style="background: #fff; border-radius: var(--radius-lg); padding: 1.25rem 1.5rem; border: 1px solid var(--border);">
            <div
                style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">
                Cobertura
            </div>
            <div
                style="font-size: 2rem; font-weight: 800; color: {{ $coverage >= 80 ? 'var(--primary)' : ($coverage >= 50 ? 'var(--warning)' : 'var(--danger)') }};">
                {{ $coverage }}%
            </div>
        </div>
        <div
            style="background: #fff; border-radius: var(--radius-lg); padding: 1.25rem 1.5rem; border: 1px solid var(--border);">
            <div
                style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem;">
                Última Aplicação
            </div>
            <div style="font-size: 1.2rem; font-weight: 700; color: var(--text-dark);">
                @if($lastApplication)
                    {{ Carbon::parse($lastApplication)->format('d/m/Y') }}
                @else
                    <span style="color: var(--text-muted); font-size: 0.9rem;">Sem registros</span>
                @endif
            </div>
            @if($avgWeight)
                <div style="font-size: 0.78rem; color: var(--text-muted);">Peso médio: {{ number_format($avgWeight, 1, ',', '.')
            }} kg
                </div>
            @endif
        </div>
    </div>

    {{-- Charts row 1: bar + donut --}}
    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
        <x-card>
            <div
                style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem;">
                Aplicações por Mês (últimos 12 meses)
            </div>
            <div style="height: 240px;">
                <canvas id="chart-vt-monthly"></canvas>
            </div>
        </x-card>

        <x-card>
            <div
                style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem;">
                Cobertura do Rebanho
            </div>
            <div style="height: 240px;">
                <canvas id="chart-vt-coverage"></canvas>
            </div>
        </x-card>
    </div>

    {{-- Chart row 2: weight trend --}}
    <x-card>
        <div
            style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem;">
            Peso Médio na Aplicação (últimos 12 meses)
        </div>
        <div style="height: 240px;">
            <canvas id="chart-vt-weight"></canvas>
        </div>
    </x-card>

    <script>
        window.__vaccineTypeData = @json($chartData);
    </script>
@endsection
