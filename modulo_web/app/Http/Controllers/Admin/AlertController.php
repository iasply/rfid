<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cattle;
use App\Models\Vaccine;
use App\Models\VaccineType;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AlertController extends Controller
{
    private const ALERT_WINDOW = 30;
    private const PER_PAGE = 15;

    public function index()
    {
        $currentMonth = now()->month;
        $typeFilter = request('type');
        $page = (int)request('page', 1);

        // Load schedule from DB — only types with a configured interval
        $scheduleQuery = VaccineType::whereNotNull('interval_days')->orderBy('name');
        if ($typeFilter) {
            $scheduleQuery->where('name', $typeFilter);
        }
        $schedule = $scheduleQuery->get();

        $alertsByType = [];
        $totalOverdue = 0;
        $totalDueSoon = 0;
        $totalNever = 0;

        foreach ($schedule as $vt) {
            $interval = $vt->interval_days;
            $threshold = $interval - self::ALERT_WINDOW;
            $seasonMonths = $vt->season_months ?? [];
            $inSeason = in_array($currentMonth, $seasonMonths);

            $lastVaxMap = Vaccine::where('vaccine_type_id', $vt->id)
                ->select('rfid_tag', DB::raw('MAX(vaccination_date) as last_vax'))
                ->groupBy('rfid_tag')
                ->pluck('last_vax', 'rfid_tag');

            $dueRfids = $lastVaxMap->filter(fn($d) => Carbon::parse($d)->startOfDay()->diffInDays(now()->startOfDay()) >= $threshold
            )->keys();

            $neverRfids = $inSeason
                ? Cattle::whereNotIn('rfid_tag', $lastVaxMap->keys())->pluck('rfid_tag')
                : collect();

            $rfids = $dueRfids->merge($neverRfids)->unique();

            if ($rfids->isEmpty()) {
                continue;
            }

            $rows = Cattle::whereIn('rfid_tag', $rfids)
                ->orderBy('name')
                ->get()
                ->map(function ($animal) use ($lastVaxMap, $interval) {
                    $lastVax = $lastVaxMap[$animal->rfid_tag] ?? null;

                    if ($lastVax === null) {
                        return [
                            'id' => $animal->id,
                            'rfid_tag' => $animal->rfid_tag,
                            'name' => $animal->name,
                            'last_vax' => null,
                            'next_due' => null,
                            'days_since' => null,
                            'days_remaining' => null,
                            'urgency' => 'never',
                        ];
                    }

                    $daysSince = (int)Carbon::parse($lastVax)->startOfDay()->diffInDays(now()->startOfDay());
                    $daysRemaining = $interval - $daysSince;
                    $nextDue = Carbon::parse($lastVax)->addDays($interval);

                    return [
                        'id' => $animal->id,
                        'rfid_tag' => $animal->rfid_tag,
                        'name' => $animal->name,
                        'last_vax' => $lastVax,
                        'next_due' => $nextDue->format('d/m/Y'),
                        'days_since' => $daysSince,
                        'days_remaining' => $daysRemaining,
                        'urgency' => $daysRemaining <= 0 ? 'overdue' : 'due_soon',
                    ];
                })
                ->sortBy(fn($r) => match ($r['urgency']) {
                    'overdue' => 0,
                    'due_soon' => 1,
                    default => 2,
                })
                ->values();

            $total = $rows->count();
            $totalOverdue += $rows->where('urgency', 'overdue')->count();
            $totalDueSoon += $rows->where('urgency', 'due_soon')->count();
            $totalNever += $rows->where('urgency', 'never')->count();

            if ($typeFilter) {
                $paginator = new LengthAwarePaginator(
                    $rows->forPage($page, self::PER_PAGE)->values(),
                    $total,
                    self::PER_PAGE,
                    $page,
                    ['path' => request()->url(), 'query' => request()->except('page')]
                );
                $displayRows = collect($paginator->items());
            } else {
                $paginator = null;
                $displayRows = $rows->take(self::PER_PAGE);
            }

            $alertsByType[$vt->name] = [
                'description' => $vt->description ?? '',
                'interval' => $interval,
                'in_season' => $inSeason,
                'total' => $total,
                'has_more' => !$typeFilter && $total > self::PER_PAGE,
                'paginator' => $paginator,
                'rows' => $displayRows,
            ];
        }

        // Vaccine type names for the filter dropdown (all types with an interval)
        $vaccineTypeNames = VaccineType::whereNotNull('interval_days')->orderBy('name')->pluck('name');

        return view('admin.alerts.index', [
            'alertsByType' => $alertsByType,
            'totalOverdue' => $totalOverdue,
            'totalDueSoon' => $totalDueSoon,
            'totalNever' => $totalNever,
            'vaccineTypes' => $vaccineTypeNames,
            'typeFilter' => $typeFilter,
        ]);
    }
}
