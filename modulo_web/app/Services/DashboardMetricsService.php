<?php

namespace App\Services;

use App\Models\Cattle;
use App\Models\User;
use App\Models\Vaccine;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardMetricsService
{
    public function getStats(): array
    {
        return [
            'vets'     => User::where('is_veterinarian', true)->count(),
            'cattle'   => Cattle::count(),
            'vaccines' => Vaccine::count(),
        ];
    }

    public function getInsights(): array
    {
        $totalCattle      = Cattle::count();
        $vaccinatedCattle = DB::table('cattle')
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))->from('vaccines')->whereColumn('vaccines.rfid_tag', 'cattle.rfid_tag');
            })->count();

        $topVaccine = DB::table('vaccines')
            ->join('vaccine_types', 'vaccines.vaccine_type_id', '=', 'vaccine_types.id')
            ->select('vaccine_types.name', DB::raw('COUNT(*) as total'))
            ->groupBy('vaccine_types.id', 'vaccine_types.name')
            ->orderByDesc('total')
            ->value('vaccine_types.name') ?? '—';

        $topVetRow = DB::table('vaccines')
            ->join('users', 'vaccines.user_id', '=', 'users.id')
            ->select('users.name', DB::raw('COUNT(*) as total'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total')
            ->first();

        $neverVaccinated = DB::table('cattle')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))->from('vaccines')->whereColumn('vaccines.rfid_tag', 'cattle.rfid_tag');
            })->count();

        $avgDaysSinceVax = DB::table('cattle')
            ->join(
                DB::raw('(SELECT rfid_tag, MAX(vaccination_date) as last_vax FROM vaccines GROUP BY rfid_tag) as lv'),
                'cattle.rfid_tag', '=', 'lv.rfid_tag'
            )
            ->selectRaw("ROUND(AVG(JULIANDAY('now') - JULIANDAY(lv.last_vax))) as avg_days")
            ->value('avg_days');

        return [
            'coverage_pct'       => $totalCattle > 0 ? round(($vaccinatedCattle / $totalCattle) * 100, 1) : 0,
            'avg_weight'         => round((float) Cattle::avg('weight'), 2),
            'top_vaccine'        => $topVaccine,
            'top_vet'            => $topVetRow
                ? ['name' => $topVetRow->name, 'count' => (int) $topVetRow->total]
                : ['name' => '—', 'count' => 0],
            'never_vaccinated'   => $neverVaccinated,
            'avg_days_since_vax' => $avgDaysSinceVax !== null ? (int) $avgDaysSinceVax : null,
        ];
    }

    public function getChartPeriods(): array
    {
        return [
            '3m'  => $this->buildMonthly(3),
            '6m'  => $this->buildMonthly(6),
            '12m' => $this->buildMonthly(12),
        ];
    }

    public function getCharts(): array
    {
        return [
            'vaccineTypes'             => $this->buildVaccineTypesChart(),
            'cattlePerVet'             => $this->buildCattlePerVetChart(),
            'vaccinesPerWorkstation'   => $this->buildVaccinesPerWorkstationChart(),
            'weightEvolution'          => $this->buildWeightEvolutionChart(),
            'weightByVaccineType'      => $this->buildWeightByVaccineTypeChart(),
            'seasonalVaccinations'     => $this->buildSeasonalChart(),
            'vaccineTypeByWorkstation' => $this->buildVaccineTypeByWorkstationChart(),
            'weightByWorkstation'      => $this->buildWeightByWorkstationChart(),
        ];
    }

    public function getRecentVaccinations(): Collection
    {
        return DB::table('vaccines')
            ->join('cattle', 'vaccines.rfid_tag', '=', 'cattle.rfid_tag')
            ->join('vaccine_types', 'vaccines.vaccine_type_id', '=', 'vaccine_types.id')
            ->leftJoin('users', 'vaccines.user_id', '=', 'users.id')
            ->select(
                'cattle.name as animal',
                'vaccine_types.name as vaccine_type',
                'vaccines.current_weight',
                'vaccines.vaccination_date',
                DB::raw("COALESCE(users.name, '—') as vet")
            )
            ->orderByDesc('vaccines.vaccination_date')
            ->orderByDesc('vaccines.id')
            ->limit(10)
            ->get();
    }

    private function buildMonthly(int $months): array
    {
        $raw = DB::table('vaccines')
            ->select(
                DB::raw("strftime('%Y-%m', vaccination_date) as month"),
                DB::raw('COUNT(*) as total')
            )
            ->where('vaccination_date', '>=', now()->subMonths($months - 1)->startOfMonth()->toDateString())
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $labels = $values = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $key      = now()->subMonths($i)->format('Y-m');
            $labels[] = now()->subMonths($i)->translatedFormat('M/y');
            $values[] = $raw->has($key) ? (int) $raw[$key]->total : 0;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    private function buildVaccineTypesChart(): array
    {
        $data = DB::table('vaccines')
            ->join('vaccine_types', 'vaccines.vaccine_type_id', '=', 'vaccine_types.id')
            ->select('vaccine_types.name as vaccine_type', DB::raw('COUNT(*) as total'))
            ->groupBy('vaccine_types.id', 'vaccine_types.name')
            ->orderByDesc('total')
            ->get();

        return [
            'labels' => $data->pluck('vaccine_type')->toArray(),
            'values' => $data->pluck('total')->map(fn($v) => (int) $v)->toArray(),
        ];
    }

    private function buildCattlePerVetChart(): array
    {
        $data = DB::table('cattle')
            ->join('users', 'cattle.user_id', '=', 'users.id')
            ->where('users.is_veterinarian', true)
            ->select('users.name', DB::raw('COUNT(cattle.id) as total'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total')
            ->get();

        return [
            'labels' => $data->pluck('name')->toArray(),
            'values' => $data->pluck('total')->map(fn($v) => (int) $v)->toArray(),
        ];
    }

    private function buildVaccinesPerWorkstationChart(): array
    {
        $data = DB::table('vaccines')
            ->leftJoin('workstations', 'vaccines.workstation_id', '=', 'workstations.id')
            ->select(
                DB::raw("COALESCE(workstations.desc, 'Sem Estação') as station"),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('workstations.id', 'workstations.desc')
            ->orderByDesc('total')
            ->get();

        return [
            'labels' => $data->pluck('station')->toArray(),
            'values' => $data->pluck('total')->map(fn($v) => (int) $v)->toArray(),
        ];
    }

    private function buildWeightEvolutionChart(): array
    {
        $raw = DB::table('vaccines')
            ->select(
                DB::raw("strftime('%Y-%m', vaccination_date) as month"),
                DB::raw('ROUND(AVG(current_weight), 1) as avg_weight')
            )
            ->where('vaccination_date', '>=', now()->subMonths(11)->startOfMonth()->toDateString())
            ->where('current_weight', '>', 0)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $labels = $values = [];
        for ($i = 11; $i >= 0; $i--) {
            $key      = now()->subMonths($i)->format('Y-m');
            $labels[] = now()->subMonths($i)->translatedFormat('M/y');
            $values[] = $raw->has($key) ? (float) $raw[$key]->avg_weight : null;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    private function buildWeightByVaccineTypeChart(): array
    {
        $data = DB::table('vaccines')
            ->join('vaccine_types', 'vaccines.vaccine_type_id', '=', 'vaccine_types.id')
            ->select('vaccine_types.name as vaccine_type', DB::raw('ROUND(AVG(current_weight), 1) as avg_weight'))
            ->where('current_weight', '>', 0)
            ->groupBy('vaccine_types.id', 'vaccine_types.name')
            ->orderByDesc('avg_weight')
            ->get();

        return [
            'labels' => $data->pluck('vaccine_type')->toArray(),
            'values' => $data->pluck('avg_weight')->map(fn($v) => (float) $v)->toArray(),
        ];
    }

    private function buildSeasonalChart(): array
    {
        $monthNames = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];

        $raw = DB::table('vaccines')
            ->select(DB::raw("strftime('%m', vaccination_date) as month_num"), DB::raw('COUNT(*) as total'))
            ->groupBy('month_num')
            ->orderBy('month_num')
            ->get()
            ->keyBy('month_num');

        $values = [];
        for ($m = 1; $m <= 12; $m++) {
            $key      = str_pad($m, 2, '0', STR_PAD_LEFT);
            $values[] = $raw->has($key) ? (int) $raw[$key]->total : 0;
        }

        return ['labels' => $monthNames, 'values' => $values];
    }

    private function buildVaccineTypeByWorkstationChart(): array
    {
        $rows = DB::table('vaccines')
            ->leftJoin('workstations', 'vaccines.workstation_id', '=', 'workstations.id')
            ->join('vaccine_types', 'vaccines.vaccine_type_id', '=', 'vaccine_types.id')
            ->select(
                DB::raw("COALESCE(workstations.desc, 'Sem Estação') as station"),
                'vaccine_types.name as vaccine_type',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('workstations.id', 'workstations.desc', 'vaccine_types.id', 'vaccine_types.name')
            ->orderBy('station')
            ->get();

        $stationLabels  = $rows->pluck('station')->unique()->values()->toArray();
        $vaccineTypeSet = $rows->pluck('vaccine_type')->unique()->values()->toArray();
        $datasets       = [];

        foreach ($vaccineTypeSet as $type) {
            $values = [];
            foreach ($stationLabels as $station) {
                $row      = $rows->first(fn($r) => $r->station === $station && $r->vaccine_type === $type);
                $values[] = $row ? (int) $row->total : 0;
            }
            $datasets[] = ['label' => $type, 'values' => $values];
        }

        return ['labels' => $stationLabels, 'datasets' => $datasets];
    }

    private function buildWeightByWorkstationChart(): array
    {
        $data = DB::table('vaccines')
            ->leftJoin('workstations', 'vaccines.workstation_id', '=', 'workstations.id')
            ->select(
                DB::raw("COALESCE(workstations.desc, 'Sem Estação') as station"),
                DB::raw('ROUND(AVG(current_weight), 1) as avg_weight')
            )
            ->where('current_weight', '>', 0)
            ->groupBy('workstations.id', 'workstations.desc')
            ->orderByDesc('avg_weight')
            ->get();

        return [
            'labels' => $data->pluck('station')->toArray(),
            'values' => $data->pluck('avg_weight')->map(fn($v) => (float) $v)->toArray(),
        ];
    }
}
