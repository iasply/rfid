<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cattle;
use App\Models\User;
use App\Models\Vaccine;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ── KPI counters ──────────────────────────────────────────────────────
        $stats = [
            'vets' => User::where('is_veterinarian', true)->count(),
            'cattle' => Cattle::count(),
            'vaccines' => Vaccine::count(),
        ];

        // ── Insight KPIs ──────────────────────────────────────────────────────

        $vaccinatedCattle = DB::table('cattle')
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('vaccines')
                    ->whereColumn('vaccines.rfid_tag', 'cattle.rfid_tag');
            })
            ->count();
        $coveragePct = $stats['cattle'] > 0
            ? round(($vaccinatedCattle / $stats['cattle']) * 100, 1)
            : 0;

        $avgWeight = round((float)Cattle::avg('weight'), 2);

        // Most-used vaccine type (join for name)
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
        $topVet = $topVetRow
            ? ['name' => $topVetRow->name, 'count' => (int)$topVetRow->total]
            : ['name' => '—', 'count' => 0];

        $neverVaccinated = DB::table('cattle')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('vaccines')
                    ->whereColumn('vaccines.rfid_tag', 'cattle.rfid_tag');
            })
            ->count();

        $avgDaysSinceVax = DB::table('cattle')
            ->join(
                DB::raw('(SELECT rfid_tag, MAX(vaccination_date) as last_vax FROM vaccines GROUP BY rfid_tag) as lv'),
                'cattle.rfid_tag', '=', 'lv.rfid_tag'
            )
            ->selectRaw("ROUND(AVG(JULIANDAY('now') - JULIANDAY(lv.last_vax))) as avg_days")
            ->value('avg_days');

        $insights = [
            'coverage_pct' => $coveragePct,
            'avg_weight' => $avgWeight,
            'top_vaccine' => $topVaccine,
            'top_vet' => $topVet,
            'never_vaccinated' => $neverVaccinated,
            'avg_days_since_vax' => $avgDaysSinceVax !== null ? (int)$avgDaysSinceVax : null,
        ];

        // ── Chart datasets ────────────────────────────────────────────────────

        $buildMonthly = function (int $months): array {
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

            $labels = [];
            $values = [];
            for ($i = $months - 1; $i >= 0; $i--) {
                $key = now()->subMonths($i)->format('Y-m');
                $labels[] = now()->subMonths($i)->translatedFormat('M/y');
                $values[] = $raw->has($key) ? (int)$raw[$key]->total : 0;
            }
            return ['labels' => $labels, 'values' => $values];
        };

        $chartPeriods = [
            '3m' => $buildMonthly(3),
            '6m' => $buildMonthly(6),
            '12m' => $buildMonthly(12),
        ];

        // Vaccine type distribution (join for name)
        $vaccineTypesData = DB::table('vaccines')
            ->join('vaccine_types', 'vaccines.vaccine_type_id', '=', 'vaccine_types.id')
            ->select('vaccine_types.name as vaccine_type', DB::raw('COUNT(*) as total'))
            ->groupBy('vaccine_types.id', 'vaccine_types.name')
            ->orderByDesc('total')
            ->get();
        $chartVaccineTypes = [
            'labels' => $vaccineTypesData->pluck('vaccine_type')->toArray(),
            'values' => $vaccineTypesData->pluck('total')->map(fn($v) => (int)$v)->toArray(),
        ];

        // Cattle per veterinarian
        $cattlePerVet = DB::table('cattle')
            ->join('users', 'cattle.user_id', '=', 'users.id')
            ->where('users.is_veterinarian', true)
            ->select('users.name', DB::raw('COUNT(cattle.id) as total'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total')
            ->get();
        $chartCattlePerVet = [
            'labels' => $cattlePerVet->pluck('name')->toArray(),
            'values' => $cattlePerVet->pluck('total')->map(fn($v) => (int)$v)->toArray(),
        ];

        // Vaccines per workstation
        $vaccinesPerWorkstation = DB::table('vaccines')
            ->leftJoin('workstations', 'vaccines.workstation_id', '=', 'workstations.id')
            ->select(
                DB::raw("COALESCE(workstations.desc, 'Sem Estação') as station"),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('workstations.id', 'workstations.desc')
            ->orderByDesc('total')
            ->get();
        $chartVaccinesPerWorkstation = [
            'labels' => $vaccinesPerWorkstation->pluck('station')->toArray(),
            'values' => $vaccinesPerWorkstation->pluck('total')->map(fn($v) => (int)$v)->toArray(),
        ];

        // Average weight evolution by month
        $weightRaw = DB::table('vaccines')
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

        $weightLabels = [];
        $weightValues = [];
        for ($i = 11; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $weightLabels[] = now()->subMonths($i)->translatedFormat('M/y');
            $weightValues[] = $weightRaw->has($key) ? (float)$weightRaw[$key]->avg_weight : null;
        }
        $chartWeightEvolution = ['labels' => $weightLabels, 'values' => $weightValues];

        // Average weight per workstation
        $weightByStation = DB::table('vaccines')
            ->leftJoin('workstations', 'vaccines.workstation_id', '=', 'workstations.id')
            ->select(
                DB::raw("COALESCE(workstations.desc, 'Sem Estação') as station"),
                DB::raw('ROUND(AVG(current_weight), 1) as avg_weight')
            )
            ->where('current_weight', '>', 0)
            ->groupBy('workstations.id', 'workstations.desc')
            ->orderByDesc('avg_weight')
            ->get();
        $chartWeightByWorkstation = [
            'labels' => $weightByStation->pluck('station')->toArray(),
            'values' => $weightByStation->pluck('avg_weight')->map(fn($v) => (float)$v)->toArray(),
        ];

        // Vaccine type breakdown per workstation — stacked bar (join for name)
        $vaxByStation = DB::table('vaccines')
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

        $stationLabels = $vaxByStation->pluck('station')->unique()->values()->toArray();
        $vaccineTypeSet = $vaxByStation->pluck('vaccine_type')->unique()->values()->toArray();

        $stackedDatasets = [];
        foreach ($vaccineTypeSet as $type) {
            $values = [];
            foreach ($stationLabels as $station) {
                $row = $vaxByStation->first(fn($r) => $r->station === $station && $r->vaccine_type === $type);
                $values[] = $row ? (int)$row->total : 0;
            }
            $stackedDatasets[] = ['label' => $type, 'values' => $values];
        }

        $chartVaccineTypeByWorkstation = [
            'labels' => $stationLabels,
            'datasets' => $stackedDatasets,
        ];

        // Average weight per vaccine type (join for name)
        $weightByVaccine = DB::table('vaccines')
            ->join('vaccine_types', 'vaccines.vaccine_type_id', '=', 'vaccine_types.id')
            ->select('vaccine_types.name as vaccine_type', DB::raw('ROUND(AVG(current_weight), 1) as avg_weight'))
            ->where('current_weight', '>', 0)
            ->groupBy('vaccine_types.id', 'vaccine_types.name')
            ->orderByDesc('avg_weight')
            ->get();
        $chartWeightByVaccineType = [
            'labels' => $weightByVaccine->pluck('vaccine_type')->toArray(),
            'values' => $weightByVaccine->pluck('avg_weight')->map(fn($v) => (float)$v)->toArray(),
        ];

        // Seasonal vaccination pattern
        $monthNames = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        $seasonalRaw = DB::table('vaccines')
            ->select(DB::raw("strftime('%m', vaccination_date) as month_num"), DB::raw('COUNT(*) as total'))
            ->groupBy('month_num')
            ->orderBy('month_num')
            ->get()
            ->keyBy('month_num');
        $seasonalValues = [];
        for ($m = 1; $m <= 12; $m++) {
            $key = str_pad($m, 2, '0', STR_PAD_LEFT);
            $seasonalValues[] = $seasonalRaw->has($key) ? (int)$seasonalRaw[$key]->total : 0;
        }
        $chartSeasonalVaccinations = ['labels' => $monthNames, 'values' => $seasonalValues];

        // Last 10 vaccinations (join for vaccine type name)
        $recentVaccinations = DB::table('vaccines')
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

        return view('admin.dashboard', compact(
            'stats',
            'insights',
            'chartPeriods',
            'chartVaccineTypes',
            'chartCattlePerVet',
            'chartVaccinesPerWorkstation',
            'chartWeightEvolution',
            'chartWeightByVaccineType',
            'chartSeasonalVaccinations',
            'chartVaccineTypeByWorkstation',
            'chartWeightByWorkstation',
            'recentVaccinations',
        ));
    }
}
