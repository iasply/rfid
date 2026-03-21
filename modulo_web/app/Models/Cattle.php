<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cattle extends Model
{
    use HasFactory;

    protected $fillable = ['rfid_tag', 'name', 'weight', 'registration_date', 'user_id'];


    /**
     * Get the user who registered the animal.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vaccines()
    {
        return $this->hasMany(Vaccine::class, 'rfid_tag', 'rfid_tag');
    }
}
