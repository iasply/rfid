<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'vet_rfid',
        'is_veterinarian',
        'tag_hash',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function booted()
    {
        static::creating(function ($user) {
            if ($user->is_veterinarian) {
                if (!$user->vet_rfid || $user->vet_rfid === 'V') {
                    $user->vet_rfid = \App\Support\RfidGenerator::generateVetTag();
                }
            } else {
                if (!$user->vet_rfid) {
                    $user->vet_rfid = 'USER-' . (static::max('id') + 1);
                }
            }

            if ($user->vet_rfid) {
                $user->tag_hash = hash('sha256', $user->vet_rfid . config('app.tag_salt'));
            }
        });

        static::updating(function ($user) {
            if ($user->isDirty('vet_rfid') && $user->vet_rfid) {
                $user->tag_hash = hash('sha256', $user->vet_rfid . config('app.tag_salt'));
            }
        });
    }

    public function cattle()
    {
        return $this->hasMany(Cattle::class);
    }

    public function vaccinations()
    {
        return $this->hasMany(Vaccine::class);
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
