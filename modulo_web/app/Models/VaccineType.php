<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VaccineType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'interval_days',
        'season_months',
    ];

    protected $casts = [
        'season_months' => 'array',
        'interval_days' => 'integer',
    ];

    public function vaccines()
    {
        return $this->hasMany(Vaccine::class);
    }
}
