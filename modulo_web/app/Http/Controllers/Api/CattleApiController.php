<?php

namespace App\Http\Controllers\Api;

use App\DTOs\Request\Cattle\StoreCattleRequest;
use App\DTOs\Request\Cattle\UpdateCattleRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\CattleResource;
use App\Models\Cattle;
use App\Services\CattleService;
use Illuminate\Http\JsonResponse;

class CattleApiController extends Controller
{
    public function __construct(
        protected CattleService $cattleService
    )
    {
    }

    public function index(): JsonResponse
    {
        $items = Cattle::paginate(50);
        return response()->json(CattleResource::collection($items)->response()->getData(true));
    }

    public function indexWithVaccines(): JsonResponse
    {
        $items = Cattle::withCount('vaccines')->paginate(15);

        return response()->json(CattleResource::collection($items)->response()->getData(true));
    }

    public function store(StoreCattleRequest $request): JsonResponse
    {
        $cattle = $this->cattleService->createCattle($request->validated(), $request->user()?->id);

        return response()->json([
            'message' => 'Animal cadastrado via API!',
            'cattle' => new CattleResource($cattle),
        ], 201);
    }

    public function show(string $rfid_tag): JsonResponse
    {
        $cattle = Cattle::where('rfid_tag', $rfid_tag)->first();

        if (!$cattle) {
            return response()->json(['message' => 'Animal não encontrado.'], 404);
        }

        return response()->json(new CattleResource($cattle));
    }

    public function update(UpdateCattleRequest $request, Cattle $cattle): JsonResponse
    {
        $cattle = $this->cattleService->updateCattle($cattle, $request->validated());

        return response()->json([
            'message' => 'Animal atualizado via API!',
            'cattle' => new CattleResource($cattle),
        ]);
    }
}
