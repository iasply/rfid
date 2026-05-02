<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Vaccine\StoreVaccineRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\VaccineResource;
use App\Models\Vaccine;
use App\Services\VaccineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VaccineApiController extends Controller
{
    public function __construct(private VaccineService $vaccineService)
    {
    }

    public function store(StoreVaccineRequest $request): JsonResponse
    {
        $user  = $request->user();
        $token = $user->currentAccessToken();

        $vaccine = $this->vaccineService->recordVaccination(
            $request->validated(),
            $user->id,
            $token->workstation_id ?? null,
        );

        $vaccine->load('user', 'workstation');

        return response()->json([
            'message' => 'Vacinação registrada via API!',
            'vaccine' => new VaccineResource($vaccine),
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $query = Vaccine::with(['cattle', 'user', 'workstation']);

        if ($request->has('rfid_tag')) {
            $query->where('rfid_tag', $request->rfid_tag);
        }

        $vaccines = $query->latest()->paginate(50);

        return response()->json(VaccineResource::collection($vaccines)->response()->getData(true));
    }
}
