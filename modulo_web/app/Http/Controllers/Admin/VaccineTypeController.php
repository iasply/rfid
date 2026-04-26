<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cattle;
use App\Models\Vaccine;
use App\Models\VaccineType;
use Illuminate\Http\Request;

class VaccineTypeController extends Controller
{
    private const MONTH_NAMES = [
        1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
        5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
        9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
    ];

    public function index()
    {
        $vaccineTypes = VaccineType::orderBy('name')->paginate(15)->withQueryString();

        return view('admin.vaccine-types.index', compact('vaccineTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:vaccine_types,name',
            'description' => 'nullable|string|max:500',
            'interval_days' => 'nullable|integer|min:1|max:3650',
            'season_months' => 'nullable|array',
            'season_months.*' => 'integer|between:1,12',
        ]);

        $data['season_months'] = $data['season_months'] ?? null;

        VaccineType::create($data);

        return redirect()->route('admin.vaccine-types.index')
            ->with('success', 'Tipo de vacina cadastrado com sucesso!');
    }

    public function create()
    {
        return view('admin.vaccine-types.create', ['monthNames' => self::MONTH_NAMES]);
    }

    public function show(VaccineType $vaccineType)
    {
        $abbr = ['', 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        $since = now()->subMonths(11)->startOfMonth()->toDateString();

        $monthlyRaw = Vaccine::where('vaccine_type_id', $vaccineType->id)
            ->where('vaccination_date', '>=', $since)
            ->selectRaw("strftime('%Y-%m', vaccination_date) as month, COUNT(*) as cnt")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('cnt', 'month');

        $weightRaw = Vaccine::where('vaccine_type_id', $vaccineType->id)
            ->where('vaccination_date', '>=', $since)
            ->where('current_weight', '>', 0)
            ->selectRaw("strftime('%Y-%m', vaccination_date) as month, ROUND(AVG(current_weight), 1) as avg_w")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('avg_w', 'month');

        $labels = [];
        $monthly = [];
        $weightValues = [];
        for ($i = 11; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $key = $d->format('Y-m');
            $labels[] = $abbr[(int)$d->format('n')] . '/' . $d->format('y');
            $monthly[] = $monthlyRaw[$key] ?? 0;
            $weightValues[] = isset($weightRaw[$key]) ? (float)$weightRaw[$key] : null;
        }

        $totalCattle = Cattle::count();
        $vaccinatedCount = Vaccine::where('vaccine_type_id', $vaccineType->id)
            ->distinct('rfid_tag')->count('rfid_tag');
        $neverCount = max(0, $totalCattle - $vaccinatedCount);
        $coverage = $totalCattle > 0 ? round($vaccinatedCount / $totalCattle * 100) : 0;

        $totalApplications = Vaccine::where('vaccine_type_id', $vaccineType->id)->count();
        $lastApplication = Vaccine::where('vaccine_type_id', $vaccineType->id)->max('vaccination_date');
        $avgWeight = Vaccine::where('vaccine_type_id', $vaccineType->id)
            ->where('current_weight', '>', 0)->avg('current_weight');

        $chartData = [
            'monthly' => ['labels' => $labels, 'values' => $monthly],
            'coverage' => ['labels' => ['Vacinados', 'Nunca vacinados'], 'values' => [$vaccinatedCount, $neverCount]],
            'weight' => ['labels' => $labels, 'values' => $weightValues],
        ];

        return view('admin.vaccine-types.show', compact(
            'vaccineType', 'totalApplications', 'vaccinatedCount', 'totalCattle',
            'coverage', 'lastApplication', 'avgWeight', 'chartData'
        ));
    }

    public function edit(VaccineType $vaccineType)
    {
        return view('admin.vaccine-types.edit', [
            'vaccineType' => $vaccineType,
            'monthNames' => self::MONTH_NAMES,
        ]);
    }

    public function update(Request $request, VaccineType $vaccineType)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:vaccine_types,name,' . $vaccineType->id,
            'description' => 'nullable|string|max:500',
            'interval_days' => 'nullable|integer|min:1|max:3650',
            'season_months' => 'nullable|array',
            'season_months.*' => 'integer|between:1,12',
        ]);

        $data['season_months'] = $data['season_months'] ?? null;

        $vaccineType->update($data);

        return redirect()->route('admin.vaccine-types.index')
            ->with('success', 'Tipo de vacina atualizado!');
    }
}
