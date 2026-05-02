<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardMetricsService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private DashboardMetricsService $metrics)
    {
    }

    public function index(): View
    {
        $charts = $this->metrics->getCharts();

        return view('admin.dashboard', [
            'stats'                         => $this->metrics->getStats(),
            'insights'                      => $this->metrics->getInsights(),
            'chartPeriods'                  => $this->metrics->getChartPeriods(),
            'chartVaccineTypes'             => $charts['vaccineTypes'],
            'chartCattlePerVet'             => $charts['cattlePerVet'],
            'chartVaccinesPerWorkstation'   => $charts['vaccinesPerWorkstation'],
            'chartWeightEvolution'          => $charts['weightEvolution'],
            'chartWeightByVaccineType'      => $charts['weightByVaccineType'],
            'chartSeasonalVaccinations'     => $charts['seasonalVaccinations'],
            'chartVaccineTypeByWorkstation' => $charts['vaccineTypeByWorkstation'],
            'chartWeightByWorkstation'      => $charts['weightByWorkstation'],
            'recentVaccinations'            => $this->metrics->getRecentVaccinations(),
        ]);
    }
}
