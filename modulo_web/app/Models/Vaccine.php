<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vaccine extends Model
{
    use HasFactory;

    protected $fillable = [
        'rfid_tag',
        'vaccine_type_id',
        'current_weight',
        'vaccination_date',
        'user_id',
        'workstation_id',
    ];

    public function vaccineType()
    {
        return $this->belongsTo(VaccineType::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function workstation()
    {
        return $this->belongsTo(Workstation::class);
    }

    public function cattle()
    {
        return $this->belongsTo(Cattle::class, 'rfid_tag', 'rfid_tag');
    }
}
