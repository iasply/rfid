<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vaccine extends Model
{
    use HasFactory;

    protected $fillable = [
        'rfid_tag',
        'vaccine_type',
        'current_weight',
        'vaccination_date',
        'user_id',
        'workstation_id',
    ];

    /**
     * Get the user who administered the vaccine.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the workstation used for this vaccination.
     */
    public function workstation()
    {
        return $this->belongsTo(Workstation::class);
    }

    /**
     * Get the cattle (animal) associated with this vaccine record.
     */
    public function cattle()
    {
        return $this->belongsTo(Cattle::class, 'rfid_tag', 'rfid_tag');
    }
}
