<?php

namespace App\Http\Controllers\Api;

use App\Models\VaccineType;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class VaccineTypeApiController extends Controller
{
    public function index(): JsonResponse
    {
        $types = VaccineType::orderBy('name')->get(['id', 'name', 'description', 'interval_days', 'season_months']);

        return response()->json(['data' => $types]);
    }
}
