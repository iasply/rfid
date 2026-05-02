<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VaccineType\StoreVaccineTypeRequest;
use App\Http\Requests\VaccineType\UpdateVaccineTypeRequest;
use App\Models\VaccineType;
use App\Services\VaccineTypeStatsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VaccineTypeController extends Controller
{
    private const MONTH_NAMES = [
        1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março',    4 => 'Abril',
        5 => 'Maio',    6 => 'Junho',     7 => 'Julho',    8 => 'Agosto',
        9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
    ];

    public function index(): View
    {
        $q   = request('q');
        $col = request('col');

        $query = VaccineType::orderBy('name');

        if ($q) {
            if ($col === 'name') {
                $query->where('name', 'like', "%{$q}%");
            } elseif ($col === 'description') {
                $query->where('description', 'like', "%{$q}%");
            } else {
                $query->where(function ($sq) use ($q) {
                    $sq->where('name', 'like', "%{$q}%")
                       ->orWhere('description', 'like', "%{$q}%");
                });
            }
        }

        $vaccineTypes = $query->paginate(15)->withQueryString();

        return view('admin.vaccine-types.index', compact('vaccineTypes'));
    }

    public function create(): View
    {
        return view('admin.vaccine-types.create', ['monthNames' => self::MONTH_NAMES]);
    }

    public function store(StoreVaccineTypeRequest $request): RedirectResponse
    {
        VaccineType::create($request->validated());

        return redirect()->route('admin.vaccine-types.index')
            ->with('success', 'Tipo de vacina cadastrado com sucesso!');
    }

    public function show(VaccineType $vaccineType, VaccineTypeStatsService $stats): View
    {
        return view('admin.vaccine-types.show', array_merge(
            compact('vaccineType'),
            $stats->getAll($vaccineType)
        ));
    }

    public function edit(VaccineType $vaccineType): View
    {
        return view('admin.vaccine-types.edit', [
            'vaccineType' => $vaccineType,
            'monthNames'  => self::MONTH_NAMES,
        ]);
    }

    public function update(UpdateVaccineTypeRequest $request, VaccineType $vaccineType): RedirectResponse
    {
        $vaccineType->update($request->validated());

        return redirect()->route('admin.vaccine-types.index')
            ->with('success', 'Tipo de vacina atualizado!');
    }
}
