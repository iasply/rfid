<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\Request\Vaccine\StoreVaccineRequest;
use App\DTOs\Response\VaccineResponse;
use App\Http\Controllers\Controller;
use App\Models\Cattle;
use App\Models\User;
use App\Models\Vaccine;

class VaccineController extends Controller
{
    public function index()
    {
        $q   = request('q');
        $col = request('col');

        $vaccines = Vaccine::with('user', 'workstation', 'cattle')
            ->when($q, function ($query) use ($q, $col) {
                match ($col) {
                    'vaccine_type' => $query->where('vaccine_type', 'like', "%{$q}%"),
                    'rfid_tag'     => $query->where('rfid_tag', 'like', "%{$q}%"),
                    'animal'       => $query->whereHas('cattle', fn ($c) => $c->where('name', 'like', "%{$q}%")),
                    default        => $query->where(fn ($s) => $s
                        ->where('vaccine_type', 'like', "%{$q}%")
                        ->orWhere('rfid_tag', 'like', "%{$q}%")
                        ->orWhereHas('cattle', fn ($c) => $c->where('name', 'like', "%{$q}%"))),
                };
            })
            ->orderByDesc('vaccination_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Vaccine $v) => VaccineResponse::fromModel($v));

        return view('admin.vaccines.index', compact('vaccines'));
    }

    public function store(StoreVaccineRequest $request)
    {
        $vaccine = Vaccine::create(array_merge(
            $request->validated(),
            ['user_id' => auth()->id()],
        ));

        Cattle::where('rfid_tag', $request->rfid_tag)
            ->update(['weight' => $request->current_weight]);

        return redirect()->route('admin.vaccines.index')->with('success', 'Vacinação registrada!');
    }

    public function create()
    {
        $gattos = Cattle::all();
        $vets = User::where('is_veterinarian', true)->get();

        return view('admin.vaccines.create', compact('gattos', 'vets'));
    }
}
