<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Request\Vaccine\StoreVaccineRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\VaccineResource;
use App\Models\Cattle;
use App\Models\Vaccine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VaccineApiController extends Controller
{
    public function store(StoreVaccineRequest $request): JsonResponse
    {
        $user = $request->user();
        $token = $user->currentAccessToken();

        $vaccine = DB::transaction(function () use ($request, $user, $token) {
            $vaccine = Vaccine::create(array_merge(
                $request->validated(),
                [
                    'user_id' => $user->id,
                    'workstation_id' => $token->workstation_id ?? null,
                ],
            ));

            Cattle::where('rfid_tag', $request->rfid_tag)
                ->update(['weight' => $request->current_weight]);

            return $vaccine;
        });

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
