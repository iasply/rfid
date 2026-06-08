@php use Carbon\Carbon; @endphp
@extends('layouts.app')

@section('content')

    {{-- Dados dos charts injetados de forma síncrona (antes do módulo Vite executar) --}}
    <script>
        window.__dashboardData = {
            periods: @json($chartPeriods),
            vaccineTypes: @json($chartVaccineTypes),
            cattlePerVet: @json($chartCattlePerVet),
            vaccinesPerWorkstation: @json($chartVaccinesPerWorkstation),
            weightEvolution: @json($chartWeightEvolution),
            weightByVaccineType: @json($chartWeightByVaccineType),
            seasonalVaccinations: @json($chartSeasonalVaccinations),
            vaccineTypeByWorkstation: @json($chartVaccineTypeByWorkstation),
            weightByWorkstation: @json($chartWeightByWorkstation),
        };
    </script>

    {{-- Hero Banner --}}
    <div class="hero-banner">
        <div class="hero-banner__left">
            <p class="hero-banner__greeting">Bem-vindo de volta, {{ Auth::user()->name }}</p>
            <h1 class="hero-banner__title">Painel de Controle</h1>
            <p class="hero-banner__sub">Monitoramento de saúde animal e rastreamento de rebanho via RFID</p>
        </div>
        <div class="hero-banner__right">
            <div class="hero-banner__stat">
                <span class="hero-banner__stat-value">{{ $stats['cattle'] }}</span>
                <span class="hero-banner__stat-label">Animais</span>
            </div>
            <div class="hero-banner__divider"></div>
            <div class="hero-banner__stat">
                <span class="hero-banner__stat-value">{{ $insights['coverage_pct'] }}%</span>
                <span class="hero-banner__stat-label">Cobertura</span>
            </div>
            <div class="hero-banner__divider"></div>
            <div class="hero-banner__stat">
                <span class="hero-banner__stat-value">{{ $stats['vaccines'] }}</span>
                <span class="hero-banner__stat-label">Vacinas</span>
            </div>
        </div>
    </div>

    {{-- Section: Totalizadores --}}
    <div class="section-label">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="3" y="3" width="7" height="7" rx="1"/>
            <rect x="14" y="3" width="7" height="7" rx="1"/>
            <rect x="14" y="14" width="7" height="7" rx="1"/>
            <rect x="3" y="14" width="7" height="7" rx="1"/>
        </svg>
        Totalizadores do Sistema
    </div>

    {{-- Row 1 — KPI Counters --}}
    <div class="kpi-grid">

        <div class="card kpi-card kpi-primary">
            <div class="kpi-icon-bg">
                <svg width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25"
                     aria-hidden="true">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </div>
            <p class="kpi-label">Veterinários</p>
            <p class="kpi-value" data-testid="dashboard-vets-count">{{ $stats['vets'] }}</p>
            <a href="{{ route('admin.veterinarians.index') }}" class="kpi-link">Ver todos →</a>
        </div>

        <div class="card kpi-card kpi-success">
            <div class="kpi-icon-bg">
                <svg width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25"
                     aria-hidden="true">
                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                    <circle cx="7" cy="7" r="1.5" fill="currentColor" stroke="none"/>
                </svg>
            </div>
            <p class="kpi-label">Animais (Gado)</p>
            <p class="kpi-value" data-testid="dashboard-cattle-count">{{ $stats['cattle'] }}</p>
            <a href="{{ route('admin.cattle.index') }}" class="kpi-link">Ver todos →</a>
        </div>

        <div class="card kpi-card kpi-warning">
            <div class="kpi-icon-bg">
                <svg width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25"
                     aria-hidden="true">
                    <path d="m18 2 4 4"/>
                    <path d="m17 7 3-3"/>
                    <path d="M19 9 8.7 19.3c-1 1-2.5 1-3.4 0l-.6-.6c-1-1-1-2.5 0-3.4L15 5"/>
                    <path d="m9 11 4 4"/>
                    <path d="m5 19-3 3"/>
                    <path d="m14 4 6 6"/>
                </svg>
            </div>
            <p class="kpi-label">Vacinas Aplicadas</p>
            <p class="kpi-value" data-testid="dashboard-vaccines-count">{{ $stats['vaccines'] }}</p>
            <a href="{{ route('admin.vaccines.index') }}" class="kpi-link">Histórico →</a>
        </div>

    </div>

    {{-- Section: Indicadores --}}
    <div class="section-label" style="margin-top:0.5rem;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
        </svg>
        Indicadores de Saúde do Rebanho
    </div>

    {{-- Row 2 — Insight KPIs --}}
    <div class="kpi-grid kpi-grid--6">

        <div class="card kpi-card kpi-light">
            <div class="kpi-icon-bg">
                <svg width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25"
                     aria-hidden="true">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
            </div>
            <p class="kpi-label">Cobertura Vacinal</p>
            <p class="kpi-value" style="color:var(--primary);">{{ $insights['coverage_pct'] }}%</p>
            <span class="kpi-sub">Animais com ao menos 1 vacina</span>
        </div>

        <div class="card kpi-card kpi-accent">
            <div class="kpi-icon-bg">
                <svg width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25"
                     aria-hidden="true">
                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                    <line x1="3" x2="21" y1="6" y2="6"/>
                    <path d="M16 10a4 4 0 0 1-8 0"/>
                </svg>
            </div>
            <p class="kpi-label">Peso Médio do Rebanho</p>
            <p class="kpi-value" style="color:var(--accent);">
                {{ number_format($insights['avg_weight'], 1, ',', '.') }} kg
            </p>
            <span class="kpi-sub">Média atual do cadastro</span>
        </div>

        <div class="card kpi-card kpi-secondary">
            <div class="kpi-icon-bg">
                <svg width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25"
                     aria-hidden="true">
                    <polygon
                        points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                </svg>
            </div>
            <p class="kpi-label">Vacina Mais Usada</p>
            <p class="kpi-value kpi-value--sm">{{ $insights['top_vaccine'] }}</p>
            <span class="kpi-sub">Maior número de aplicações</span>
        </div>

        <div class="card kpi-card kpi-primary">
            <div class="kpi-icon-bg">
                <svg width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25"
                     aria-hidden="true">
                    <circle cx="12" cy="8" r="6"/>
                    <path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/>
                </svg>
            </div>
            <p class="kpi-label">Vet Mais Ativo</p>
            <p class="kpi-value kpi-value--sm">{{ $insights['top_vet']['name'] }}</p>
            <span class="kpi-sub">{{ $insights['top_vet']['count'] }} vacinas aplicadas</span>
        </div>

        <div class="card kpi-card {{ $insights['never_vaccinated'] > 0 ? 'kpi-danger' : 'kpi-light' }}">
            <div class="kpi-icon-bg">
                <svg width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25"
                     aria-hidden="true">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" x2="12" y1="8" y2="12"/>
                    <line x1="12" x2="12.01" y1="16" y2="16"/>
                </svg>
            </div>
            <p class="kpi-label">Sem Nenhuma Vacina</p>
            <p class="kpi-value"
               style="color:{{ $insights['never_vaccinated'] > 0 ? 'var(--danger)' : 'var(--primary)' }};">
                {{ $insights['never_vaccinated'] }}
            </p>
            <span
                class="kpi-sub">{{ $insights['never_vaccinated'] > 0 ? 'Animais em risco' : 'Rebanho protegido' }}</span>
        </div>

        <div class="card kpi-card kpi-secondary">
            <div class="kpi-icon-bg">
                <svg width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25"
                     aria-hidden="true">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <p class="kpi-label">Dias Desde Última Vacina</p>
            <p class="kpi-value" style="color:var(--secondary);">
                @if($insights['avg_days_since_vax'] !== null)
                    {{ $insights['avg_days_since_vax'] }}
                @else
                    —
                @endif
            </p>
            <span class="kpi-sub">Média do rebanho vacinado</span>
        </div>

    </div>

    {{-- Section: Análises --}}
    <div class="section-label" style="margin-top:0.5rem;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <line x1="18" y1="20" x2="18" y2="10"/>
            <line x1="12" y1="20" x2="12" y2="4"/>
            <line x1="6" y1="20" x2="6" y2="14"/>
        </svg>
        Análises e Gráficos
    </div>

    {{-- Row 3 — Charts --}}
    <div class="chart-grid">

        {{-- Monthly vaccinations with period filter --}}
        <div class="card chart-card">
            <div class="chart-card-header">
                <p class="chart-title">Vacinações por Mês</p>
                <div class="period-filter" role="group" aria-label="Período">
                    <button class="period-btn" data-period="3m">3 Meses</button>
                    <button class="period-btn" data-period="6m">6 Meses</button>
                    <button class="period-btn period-btn--active" data-period="12m">12 Meses</button>
                </div>
            </div>
            <div class="chart-wrap">
                <canvas id="chart-monthly-vaccinations"></canvas>
            </div>
        </div>

        {{-- Vaccine type doughnut --}}
        <div class="card chart-card">
            <p class="chart-title">Distribuição por Tipo de Vacina</p>
            <div class="chart-wrap">
                <canvas id="chart-vaccine-types"></canvas>
            </div>
        </div>

        {{-- Weight evolution --}}
        <div class="card chart-card">
            <p class="chart-title">
                Evolução do Peso Médio
                <span class="chart-subtitle">(peso na vacinação, kg)</span>
            </p>
            <div class="chart-wrap">
                <canvas id="chart-weight-evolution"></canvas>
            </div>
        </div>

        {{-- Cattle per vet --}}
        <div class="card chart-card">
            <p class="chart-title">Animais por Veterinário</p>
            <div class="chart-wrap">
                <canvas id="chart-cattle-per-vet"></canvas>
            </div>
        </div>

        {{-- Vaccines per workstation --}}
        <div class="card chart-card">
            <p class="chart-title">Vacinas por Estação de Trabalho</p>
            <div class="chart-wrap">
                <canvas id="chart-vaccines-per-workstation"></canvas>
            </div>
        </div>

        {{-- Average weight per vaccine type --}}
        <div class="card chart-card">
            <p class="chart-title">Peso Médio por Tipo de Vacina
                <span class="chart-subtitle">(kg na vacinação)</span>
            </p>
            <div class="chart-wrap">
                <canvas id="chart-weight-by-vaccine"></canvas>
            </div>
        </div>

        {{-- Seasonal vaccination pattern --}}
        <div class="card chart-card">
            <p class="chart-title">Sazonalidade das Vacinações
                <span class="chart-subtitle">(ocorrências por mês do ano)</span>
            </p>
            <div class="chart-wrap">
                <canvas id="chart-seasonal"></canvas>
            </div>
        </div>

        {{-- Average weight per workstation --}}
        <div class="card chart-card">
            <p class="chart-title">Peso Médio por Estação de Trabalho
                <span class="chart-subtitle">(kg na vacinação)</span>
            </p>
            <div class="chart-wrap">
                <canvas id="chart-weight-by-workstation"></canvas>
            </div>
        </div>

        {{-- Vaccine type breakdown per workstation --}}
        <div class="card chart-card" style="grid-column: 1 / -1;">
            <p class="chart-title">Tipos de Vacina por Estação de Trabalho</p>
            <div class="chart-wrap" style="min-height:300px;">
                <canvas id="chart-vaccine-type-by-workstation"></canvas>
            </div>
        </div>

    </div>

    {{-- Section: Atividade Recente --}}
    <div class="section-label" style="margin-top:0.5rem;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="10"/>
            <polyline points="12 6 12 12 16 14"/>
        </svg>
        Atividade Recente
    </div>

    {{-- Recent Activity --}}
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="chart-card-header" style="margin-bottom:1rem;">
            <p class="chart-title" style="margin:0;">Últimas Vacinações</p>
            <input
                type="search"
                id="recent-search"
                placeholder="Filtrar por animal ou vacina…"
                class="recent-search-input"
            >
        </div>

        <div class="table-wrap">
            <table class="activity-table" id="recent-table">
                <thead>
                <tr>
                    <th>Data</th>
                    <th>Animal</th>
                    <th>Vacina</th>
                    <th>Veterinário</th>
                    <th>Peso na Vacinação</th>
                </tr>
                </thead>
                <tbody>
                @forelse($recentVaccinations as $row)
                    <tr>
                        <td>{{ Carbon::parse($row->vaccination_date)->format('d/m/Y') }}</td>
                        <td><strong>{{ $row->animal }}</strong></td>
                        <td><span class="badge-vaccine">{{ $row->vaccine_type }}</span></td>
                        <td>{{ $row->vet }}</td>
                        <td>{{ number_format($row->current_weight, 1, ',', '.') }} kg</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center; color:var(--text-muted); padding:2rem;">
                            Nenhuma vacinação registrada ainda.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="quick-actions-card">
        <div class="quick-actions-card__header">
            <div>
                <h3 class="quick-actions-card__title">Ações Rápidas</h3>
                <p class="quick-actions-card__sub">Cadastre novos registros no sistema</p>
            </div>
            <svg class="quick-actions-card__icon" width="32" height="32" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="16"/>
                <line x1="8" y1="12" x2="16" y2="12"/>
            </svg>
        </div>
        <div class="quick-actions">
            <a href="{{ route('admin.veterinarians.create') }}" class="btn btn-primary"
               style="text-decoration:none;" data-testid="dashboard-new-vet-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                     aria-hidden="true">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Novo Veterinário
            </a>
            <a href="{{ route('admin.cattle.create') }}" class="btn btn-success"
               style="text-decoration:none;" data-testid="dashboard-new-cattle-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                     aria-hidden="true">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Novo Animal
            </a>
        </div>
    </div>

@endsection
