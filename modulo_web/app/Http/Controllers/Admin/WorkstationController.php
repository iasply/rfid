<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\Response\WorkstationResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Workstation\StoreWorkstationRequest;
use App\Http\Requests\Workstation\UpdateWorkstationRequest;
use App\Models\Workstation;

class WorkstationController extends Controller
{
    public function index()
    {
        $q = request('q');
        $col = request('col');

        $workstations = Workstation::when($q, function ($query) use ($q, $col) {
            match ($col) {
                'desc' => $query->where('desc', 'like', "%{$q}%"),
                'hash' => $query->where('hash', 'like', "%{$q}%"),
                default => $query->where(fn($s) => $s
                    ->where('desc', 'like', "%{$q}%")
                    ->orWhere('hash', 'like', "%{$q}%")),
            };
        })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString()
            ->through(fn(Workstation $w) => WorkstationResponse::fromModel($w));

        return view('admin.workstations.index', compact('workstations'));
    }

    public function store(StoreWorkstationRequest $request)
    {
        Workstation::create($request->validated());

        return redirect()->route('admin.workstations.index')
            ->with('success', 'Estação de trabalho cadastrada com sucesso.');
    }

    public function create()
    {
        return view('admin.workstations.create');
    }

    public function show(Workstation $workstation)
    {
        $dto = WorkstationResponse::fromModel($workstation);

        return view('admin.workstations.show', ['workstation' => $dto]);
    }

    public function edit(Workstation $workstation)
    {
        $dto = WorkstationResponse::fromModel($workstation);

        return view('admin.workstations.edit', ['workstation' => $dto]);
    }

    public function update(UpdateWorkstationRequest $request, Workstation $workstation)
    {
        $workstation->update($request->validated());

        return redirect()->route('admin.workstations.index')
            ->with('success', 'Estação de trabalho atualizada com sucesso.');
    }
}
