<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\Request\Cattle\StoreCattleRequest;
use App\DTOs\Request\Cattle\UpdateCattleRequest;
use App\DTOs\Response\VaccineResponse;
use App\Http\Controllers\Controller;
use App\Models\Cattle;
use App\Services\CattleService;
use Carbon\Carbon;

class CattleController extends Controller
{
    public function __construct(
        protected CattleService $cattleService
    )
    {
    }

    public function index()
    {
        $q = request('q');
        $col = request('col');

        $gattos = Cattle::with('user')
            ->when($q, function ($query) use ($q, $col) {
                match ($col) {
                    'name' => $query->where('name', 'like', "%{$q}%"),
                    'rfid_tag' => $query->where('rfid_tag', 'like', "%{$q}%"),
                    default => $query->where(fn($s) => $s
                        ->where('name', 'like', "%{$q}%")
                        ->orWhere('rfid_tag', 'like', "%{$q}%")),
                };
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.cattle.index', compact('gattos'));
    }

    public function create()
    {
        return view('admin.cattle.create');
    }

    public function store(StoreCattleRequest $request)
    {
        $this->cattleService->createCattle($request->validated(), auth()->id());

        return redirect()->route('admin.cattle.index')->with('success', 'Animal cadastrado!');
    }

    public function show(Cattle $cattle)
    {
        $cattle->load('vaccines.user', 'vaccines.workstation', 'vaccines.vaccineType');

        // Weight evolution — chronological, only records with a valid weight
        $chronological = $cattle->vaccines
            ->filter(fn($v) => $v->current_weight > 0)
            ->sortBy('vaccination_date')
            ->values();

        $chartWeightOverTime = [
            'labels' => $chronological->map(fn($v) => Carbon::parse($v->vaccination_date)->format('d/m/Y'))->toArray(),
            'values' => $chronological->map(fn($v) => (float)$v->current_weight)->toArray(),
        ];

        // Vaccine type distribution for this animal (keyed by type name)
        $typeCounts = $cattle->vaccines
            ->groupBy(fn($v) => $v->vaccineType?->name ?? 'Desconhecida')
            ->map(fn($group) => $group->count())
            ->sortDesc();

        $chartAnimalVaccineTypes = [
            'labels' => $typeCounts->keys()->toArray(),
            'values' => $typeCounts->values()->toArray(),
        ];

        $vaccines = $cattle->vaccines->map(fn($v) => VaccineResponse::fromModel($v));

        return view('admin.cattle.show', compact('cattle', 'vaccines', 'chartWeightOverTime', 'chartAnimalVaccineTypes'));
    }

    public function edit(Cattle $cattle)
    {
        return view('admin.cattle.edit', ['cattle' => $cattle]);
    }

    public function update(UpdateCattleRequest $request, Cattle $cattle)
    {
        $this->cattleService->updateCattle($cattle, $request->validated());

        return redirect()->route('admin.cattle.index')->with('success', 'Dados do animal atualizados!');
    }

    public function destroy(Cattle $cattle)
    {
        $cattle->delete();

        return redirect()->route('admin.cattle.index')->with('success', 'Registro removido.');
    }
}
