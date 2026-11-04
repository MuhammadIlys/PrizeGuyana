<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompetitionTicket extends Model
{
    use HasFactory;

    protected $table = 'competition_tickets';

    protected $fillable = [
        'competition_id',
        'ticket_number',
        'status',
        'user_id',
        'order_id',
        'assigned_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];
}
