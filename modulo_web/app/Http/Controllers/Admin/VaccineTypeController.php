<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VaccineType;
use Illuminate\Http\Request;

class VaccineTypeController extends Controller
{
    private const MONTH_NAMES = [
        1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
        5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
        9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
    ];

    public function index()
    {
        $vaccineTypes = VaccineType::orderBy('name')->paginate(15)->withQueryString();

        return view('admin.vaccine-types.index', compact('vaccineTypes'));
    }

    public function create()
    {
        return view('admin.vaccine-types.create', ['monthNames' => self::MONTH_NAMES]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:100|unique:vaccine_types,name',
            'description'   => 'nullable|string|max:500',
            'interval_days' => 'nullable|integer|min:1|max:3650',
            'season_months' => 'nullable|array',
            'season_months.*' => 'integer|between:1,12',
        ]);

        $data['season_months'] = $data['season_months'] ?? null;

        VaccineType::create($data);

        return redirect()->route('admin.vaccine-types.index')
            ->with('success', 'Tipo de vacina cadastrado com sucesso!');
    }

    public function edit(VaccineType $vaccineType)
    {
        return view('admin.vaccine-types.edit', [
            'vaccineType' => $vaccineType,
            'monthNames'  => self::MONTH_NAMES,
        ]);
    }

    public function update(Request $request, VaccineType $vaccineType)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:100|unique:vaccine_types,name,' . $vaccineType->id,
            'description'     => 'nullable|string|max:500',
            'interval_days'   => 'nullable|integer|min:1|max:3650',
            'season_months'   => 'nullable|array',
            'season_months.*' => 'integer|between:1,12',
        ]);

        $data['season_months'] = $data['season_months'] ?? null;

        $vaccineType->update($data);

        return redirect()->route('admin.vaccine-types.index')
            ->with('success', 'Tipo de vacina atualizado!');
    }
}
