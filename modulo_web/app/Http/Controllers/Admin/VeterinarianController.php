<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Veterinarian\StoreVeterinarianRequest;
use App\Http\Requests\Veterinarian\UpdateVeterinarianRequest;
use App\DTOs\Response\VaccineResponse;
use App\DTOs\Response\VeterinarianResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vaccine;
use Illuminate\Support\Facades\Hash;

class VeterinarianController extends Controller
{
    public function index()
    {
        $q = request('q');
        $col = request('col');

        $vets = User::where('is_veterinarian', true)
            ->when($q, function ($query) use ($q, $col) {
                match ($col) {
                    'name' => $query->where('name', 'like', "%{$q}%"),
                    'email' => $query->where('email', 'like', "%{$q}%"),
                    default => $query->where(fn($s) => $s
                        ->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")),
                };
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString()
            ->through(fn(User $u) => VeterinarianResponse::fromModel($u));

        return view('admin.veterinarians.index', compact('vets'));
    }

    public function store(StoreVeterinarianRequest $request)
    {
        User::create(array_merge($request->validated(), [
            'password' => Hash::make($request->password),
            'is_veterinarian' => true,
        ]));

        return redirect()->route('admin.veterinarians.index')
            ->with('success', 'Veterinário cadastrado com sucesso!');
    }

    public function create()
    {
        return view('admin.veterinarians.create');
    }

    public function show(User $veterinarian)
    {
        $dto = VeterinarianResponse::fromModel($veterinarian);

        $q = request('q');
        $col = request('col');

        $vaccinations = Vaccine::with('cattle', 'workstation', 'vaccineType')
            ->where('user_id', $veterinarian->id)
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
            ->paginate(10)
            ->withQueryString()
            ->through(fn(Vaccine $v) => VaccineResponse::fromModel($v));

        return view('admin.veterinarians.show', [
            'veterinarian' => $dto,
            'vaccinations' => $vaccinations,
        ]);
    }

    public function edit(User $veterinarian)
    {
        $dto = VeterinarianResponse::fromModel($veterinarian);

        return view('admin.veterinarians.edit', ['veterinarian' => $dto]);
    }

    public function update(UpdateVeterinarianRequest $request, User $veterinarian)
    {
        $data = $request->validated();

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $veterinarian->update($data);

        return redirect()->route('admin.veterinarians.index')
            ->with('success', 'Veterinário atualizado!');
    }

    public function destroy(User $veterinarian)
    {
        $veterinarian->delete();

        return redirect()->route('admin.veterinarians.index')
            ->with('success', 'Veterinário removido.');
    }
}
