@extends('layouts.app')

@section('content')

{{-- Dados dos charts injetados de forma síncrona (antes do módulo Vite executar) --}}
<script>
window.__dashboardData = {
    periods:                @json($chartPeriods),
    vaccineTypes:           @json($chartVaccineTypes),
    cattlePerVet:           @json($chartCattlePerVet),
    vaccinesPerWorkstation: @json($chartVaccinesPerWorkstation),
    weightEvolution:        @json($chartWeightEvolution),
    weightByVaccineType:        @json($chartWeightByVaccineType),
    seasonalVaccinations:       @json($chartSeasonalVaccinations),
    vaccineTypeByWorkstation:   @json($chartVaccineTypeByWorkstation),
    weightByWorkstation:        @json($chartWeightByWorkstation),
};
</script>

{{-- Page Header --}}
<div class="dashboard-header">
    <h2>Resumo do Sistema</h2>
    <span class="dashboard-welcome">Bem-vindo, {{ Auth::user()->name }}</span>
</div>

{{-- Row 1 — KPI Counters --}}
<div class="kpi-grid">

    <div class="card kpi-card kpi-primary">
        <p class="kpi-label">Veterinários</p>
        <p class="kpi-value" data-testid="dashboard-vets-count">{{ $stats['vets'] }}</p>
        <a href="{{ route('admin.veterinarians.index') }}" class="kpi-link">Ver todos →</a>
    </div>

    <div class="card kpi-card kpi-success">
        <p class="kpi-label">Animais (Gado)</p>
        <p class="kpi-value" data-testid="dashboard-cattle-count">{{ $stats['cattle'] }}</p>
        <a href="{{ route('admin.cattle.index') }}" class="kpi-link">Ver todos →</a>
    </div>

    <div class="card kpi-card kpi-warning">
        <p class="kpi-label">Vacinas Aplicadas</p>
        <p class="kpi-value" data-testid="dashboard-vaccines-count">{{ $stats['vaccines'] }}</p>
        <a href="{{ route('admin.vaccines.index') }}" class="kpi-link">Histórico →</a>
    </div>

</div>

{{-- Row 2 — Insight KPIs --}}
<div class="kpi-grid kpi-grid--6">

    <div class="card kpi-card kpi-light">
        <p class="kpi-label">Cobertura Vacinal</p>
        <p class="kpi-value" style="color:var(--primary);">{{ $insights['coverage_pct'] }}%</p>
        <span class="kpi-sub">Animais com ao menos 1 vacina</span>
    </div>

    <div class="card kpi-card kpi-accent">
        <p class="kpi-label">Peso Médio do Rebanho</p>
        <p class="kpi-value" style="color:var(--accent);">
            {{ number_format($insights['avg_weight'], 1, ',', '.') }} kg
        </p>
        <span class="kpi-sub">Média atual do cadastro</span>
    </div>

    <div class="card kpi-card kpi-secondary">
        <p class="kpi-label">Vacina Mais Usada</p>
        <p class="kpi-value kpi-value--sm">{{ $insights['top_vaccine'] }}</p>
        <span class="kpi-sub">Maior número de aplicações</span>
    </div>

    <div class="card kpi-card kpi-primary">
        <p class="kpi-label">Vet Mais Ativo</p>
        <p class="kpi-value kpi-value--sm">{{ $insights['top_vet']['name'] }}</p>
        <span class="kpi-sub">{{ $insights['top_vet']['count'] }} vacinas aplicadas</span>
    </div>

    <div class="card kpi-card {{ $insights['never_vaccinated'] > 0 ? 'kpi-danger' : 'kpi-light' }}">
        <p class="kpi-label">Sem Nenhuma Vacina</p>
        <p class="kpi-value" style="color:{{ $insights['never_vaccinated'] > 0 ? 'var(--danger)' : 'var(--primary)' }};">
            {{ $insights['never_vaccinated'] }}
        </p>
        <span class="kpi-sub">{{ $insights['never_vaccinated'] > 0 ? 'Animais em risco' : 'Rebanho protegido' }}</span>
    </div>

    <div class="card kpi-card kpi-secondary">
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
                        <td>{{ \Carbon\Carbon::parse($row->vaccination_date)->format('d/m/Y') }}</td>
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
<div class="card">
    <h3 style="margin-top:0; margin-bottom:1rem;">Ações Rápidas</h3>
    <div style="display:flex; gap:1rem; flex-wrap:wrap;">
        <a href="{{ route('admin.veterinarians.create') }}" class="btn btn-primary"
           style="text-decoration:none;" data-testid="dashboard-new-vet-link">+ Novo Veterinário</a>
        <a href="{{ route('admin.cattle.create') }}" class="btn btn-success"
           style="text-decoration:none;" data-testid="dashboard-new-cattle-link">+ Novo Animal</a>
    </div>
</div>

@endsection
