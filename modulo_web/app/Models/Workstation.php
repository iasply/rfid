<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Workstation extends Model
{
    use HasFactory;

    protected $fillable = [
        'hash',
        'desc',
    ];

    protected static function booted()
    {
        static::creating(function ($workstation) {
            if (!$workstation->hash) {
                $workstation->hash = 'WS-' . strtoupper(Str::random(8));
            }
        });

        static::updating(function ($workstation) {
            if ($workstation->isDirty('hash')) {
                $workstation->hash = $workstation->getOriginal('hash');
            }
        });

    }
}
