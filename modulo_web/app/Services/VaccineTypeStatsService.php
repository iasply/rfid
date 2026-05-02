<?php

namespace App\Services;

use App\Models\Cattle;
use App\Models\Vaccine;
use App\Models\VaccineType;

class VaccineTypeStatsService
{
    private const ABBR = ['', 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];

    public function getAll(VaccineType $vaccineType): array
    {
        $since = now()->subMonths(11)->startOfMonth()->toDateString();

        $totalCattle       = Cattle::count();
        $vaccinatedCount   = Vaccine::where('vaccine_type_id', $vaccineType->id)
            ->distinct('rfid_tag')->count('rfid_tag');
        $totalApplications = Vaccine::where('vaccine_type_id', $vaccineType->id)->count();
        $lastApplication   = Vaccine::where('vaccine_type_id', $vaccineType->id)->max('vaccination_date');
        $avgWeight         = Vaccine::where('vaccine_type_id', $vaccineType->id)
            ->where('current_weight', '>', 0)->avg('current_weight');

        $monthlyRaw = Vaccine::where('vaccine_type_id', $vaccineType->id)
            ->where('vaccination_date', '>=', $since)
            ->selectRaw("strftime('%Y-%m', vaccination_date) as month, COUNT(*) as cnt")
            ->groupBy('month')->orderBy('month')
            ->pluck('cnt', 'month');

        $weightRaw = Vaccine::where('vaccine_type_id', $vaccineType->id)
            ->where('vaccination_date', '>=', $since)
            ->where('current_weight', '>', 0)
            ->selectRaw("strftime('%Y-%m', vaccination_date) as month, ROUND(AVG(current_weight), 1) as avg_w")
            ->groupBy('month')->orderBy('month')
            ->pluck('avg_w', 'month');

        $labels = $monthly = $weightValues = [];
        for ($i = 11; $i >= 0; $i--) {
            $d            = now()->subMonths($i);
            $key          = $d->format('Y-m');
            $labels[]     = self::ABBR[(int) $d->format('n')] . '/' . $d->format('y');
            $monthly[]    = $monthlyRaw[$key] ?? 0;
            $weightValues[] = isset($weightRaw[$key]) ? (float) $weightRaw[$key] : null;
        }

        $neverCount = max(0, $totalCattle - $vaccinatedCount);
        $coverage   = $totalCattle > 0 ? round($vaccinatedCount / $totalCattle * 100) : 0;

        return [
            'totalApplications' => $totalApplications,
            'vaccinatedCount'   => $vaccinatedCount,
            'totalCattle'       => $totalCattle,
            'coverage'          => $coverage,
            'lastApplication'   => $lastApplication,
            'avgWeight'         => $avgWeight,
            'chartData'         => [
                'monthly'  => ['labels' => $labels, 'values' => $monthly],
                'coverage' => ['labels' => ['Vacinados', 'Nunca vacinados'], 'values' => [$vaccinatedCount, $neverCount]],
                'weight'   => ['labels' => $labels, 'values' => $weightValues],
            ],
        ];
    }
}
