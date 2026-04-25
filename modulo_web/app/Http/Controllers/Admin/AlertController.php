<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cattle;
use App\Models\Vaccine;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AlertController extends Controller
{
    private const ALERT_WINDOW = 30;
    private const PER_PAGE     = 15;

    /**
     * Brazilian cattle vaccination schedule (MAPA/Embrapa).
     *
     * interval      → recommended days between doses
     * season_months → months the vaccine is typically applied;
     *                 "never vaccinated" alerts only fire during these months
     * description   → campaign context shown to the user
     */
    private const SCHEDULE = [
        'Febre Aftosa' => [
            'interval'      => 180,
            'season_months' => [4, 5, 10, 11],
            'description'   => 'Campanha obrigatória MAPA — aplicação em maio e novembro. Dose de 2 mL subcutânea, cobertura mínima de 90%.',
        ],
        'Brucelose' => [
            'interval'      => 365,
            'season_months' => [1, 2, 3, 4, 5, 10, 11],
            'description'   => 'Obrigatória para fêmeas jovens de 3–8 meses. Prazo: 31 de maio (1º semestre) e até novembro (2º semestre). Vacinas B19 ou RB51.',
        ],
        'Raiva' => [
            'interval'      => 365,
            'season_months' => [1, 2, 3, 4, 11, 12],
            'description'   => 'Regiões endêmicas (morcegos hematófagos). Pico de risco na época das chuvas (novembro–abril). Revacinação anual.',
        ],
        'Clostridiose' => [
            'interval'      => 365,
            'season_months' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
            'description'   => 'Polivalente — cobre Carbúnculo Sintomático, Gangrena Gasosa e Botulismo. Inicial com reforço 30 dias após; revacinação anual.',
        ],
        'Carbúnculo Sintomático' => [
            'interval'      => 180,
            'season_months' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
            'description'   => 'A cada 6 meses em bovinos até 2 anos, depois anual. Comum em bovinos jovens de 3 meses a 2 anos.',
        ],
        'Leptospirose' => [
            'interval'      => 180,
            'season_months' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
            'description'   => 'A cada 6 meses para todo o rebanho. Protocolo inicial: 1ª dose + reforço após 4 semanas. Associada a abortos e infertilidade.',
        ],
        'IBR/BVD' => [
            'interval'      => 365,
            'season_months' => [2, 3, 4, 8, 9, 10],
            'description'   => 'Rhinotraqueíte Infecciosa Bovina e Diarreia Viral Bovina. Aplicar 60–30 dias antes da estação reprodutiva (março e setembro).',
        ],
        'Verminose' => [
            'interval'      => 120,
            'season_months' => [4, 5, 6, 7, 8, 9, 10, 11],
            'description'   => 'Esquema 5-7-9: maio, julho e setembro (Sul/Sudeste) ou 5-8-11 (Centro-Oeste). Animais do desmame até 24 meses.',
        ],
        'Botulismo' => [
            'interval'      => 365,
            'season_months' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
            'description'   => 'Revacinação anual. Maior risco na estação seca (junho–outubro) quando os animais ingerem carcaças ou ossos contaminados.',
        ],
    ];

    public function index()
    {
        $currentMonth = now()->month;
        $typeFilter   = request('type');
        $page         = (int) request('page', 1);

        $schedule = $typeFilter && isset(self::SCHEDULE[$typeFilter])
            ? [$typeFilter => self::SCHEDULE[$typeFilter]]
            : self::SCHEDULE;

        $alertsByType = [];
        $totalOverdue = 0;
        $totalDueSoon = 0;
        $totalNever   = 0;

        foreach ($schedule as $vaccineType => $config) {
            $interval  = $config['interval'];
            $threshold = $interval - self::ALERT_WINDOW;
            $inSeason  = in_array($currentMonth, $config['season_months']);

            $lastVaxMap = Vaccine::where('vaccine_type', $vaccineType)
                ->select('rfid_tag', DB::raw('MAX(vaccination_date) as last_vax'))
                ->groupBy('rfid_tag')
                ->pluck('last_vax', 'rfid_tag');

            $dueRfids = $lastVaxMap->filter(fn ($d) =>
                Carbon::parse($d)->startOfDay()->diffInDays(now()->startOfDay()) >= $threshold
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
                            'id'             => $animal->id,
                            'rfid_tag'       => $animal->rfid_tag,
                            'name'           => $animal->name,
                            'last_vax'       => null,
                            'next_due'       => null,
                            'days_since'     => null,
                            'days_remaining' => null,
                            'urgency'        => 'never',
                        ];
                    }

                    $daysSince     = (int) Carbon::parse($lastVax)->startOfDay()->diffInDays(now()->startOfDay());
                    $daysRemaining = $interval - $daysSince;
                    $nextDue       = Carbon::parse($lastVax)->addDays($interval);

                    return [
                        'id'             => $animal->id,
                        'rfid_tag'       => $animal->rfid_tag,
                        'name'           => $animal->name,
                        'last_vax'       => $lastVax,
                        'next_due'       => $nextDue->format('d/m/Y'),
                        'days_since'     => $daysSince,
                        'days_remaining' => $daysRemaining,
                        'urgency'        => $daysRemaining <= 0 ? 'overdue' : 'due_soon',
                    ];
                })
                ->sortBy(fn ($r) => match ($r['urgency']) {
                    'overdue'  => 0,
                    'due_soon' => 1,
                    default    => 2,
                })
                ->values();

            $total = $rows->count();
            $totalOverdue += $rows->where('urgency', 'overdue')->count();
            $totalDueSoon += $rows->where('urgency', 'due_soon')->count();
            $totalNever   += $rows->where('urgency', 'never')->count();

            // Paginate only when a single type is selected; otherwise show first page preview
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
                $paginator   = null;
                $displayRows = $rows->take(self::PER_PAGE);
            }

            $alertsByType[$vaccineType] = [
                'description' => $config['description'],
                'interval'    => $interval,
                'in_season'   => $inSeason,
                'total'       => $total,
                'has_more'    => !$typeFilter && $total > self::PER_PAGE,
                'paginator'   => $paginator,
                'rows'        => $displayRows,
            ];
        }

        return view('admin.alerts.index', [
            'alertsByType' => $alertsByType,
            'totalOverdue' => $totalOverdue,
            'totalDueSoon' => $totalDueSoon,
            'totalNever'   => $totalNever,
            'vaccineTypes' => array_keys(self::SCHEDULE),
            'typeFilter'   => $typeFilter,
        ]);
    }
}
