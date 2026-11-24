<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    protected $fillable = [
        'name',
        'email',
        'password',
        'account_number',
        'pin',
        'pin_attempts',
        'pin_locked_until',
        'google_id',
        'github_id',
        'otp',
        'otp_expires_at',
        'is_verified',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'pin_locked_until'  => 'datetime',
            'otp_expires_at'    => 'datetime',
            'is_verified'       => 'boolean',
        ];
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }
}