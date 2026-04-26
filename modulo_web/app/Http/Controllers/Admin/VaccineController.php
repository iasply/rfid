<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\Response\VaccineResponse;
use App\Http\Controllers\Controller;
use App\Models\Vaccine;

class VaccineController extends Controller
{
    public function index()
    {
        $q = request('q');
        $col = request('col');

        $vaccines = Vaccine::with('user', 'workstation', 'cattle', 'vaccineType')
            ->when($q, function ($query) use ($q, $col) {
                match ($col) {
                    'vaccine_type' => $query->whereHas('vaccineType', fn($vt) => $vt->where('name', 'like', "%{$q}%")),
                    'rfid_tag' => $query->where('rfid_tag', 'like', "%{$q}%"),
                    'animal' => $query->whereHas('cattle', fn($c) => $c->where('name', 'like', "%{$q}%")),
                    default => $query->where(fn($s) => $s
                        ->whereHas('vaccineType', fn($vt) => $vt->where('name', 'like', "%{$q}%"))
                        ->orWhere('rfid_tag', 'like', "%{$q}%")
                        ->orWhereHas('cattle', fn($c) => $c->where('name', 'like', "%{$q}%"))),
                };
            })
            ->orderByDesc('vaccination_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn(Vaccine $v) => VaccineResponse::fromModel($v));

        return view('admin.vaccines.index', compact('vaccines'));
    }
}
