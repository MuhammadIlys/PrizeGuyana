<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailVerification extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'country',
        'date_of_birth',
        'phone_number',
        'otp_hash',
        'expires_at',
        'last_sent_at',
        'attempts',
    ];

    protected $hidden = [
        'password',
        'otp_hash',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'expires_at' => 'datetime',
            'last_sent_at' => 'datetime',
            'attempts' => 'integer',
        ];
    }
}
