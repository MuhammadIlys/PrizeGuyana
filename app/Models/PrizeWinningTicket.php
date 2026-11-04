<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrizeWinningTicket extends Model
{
    protected $table = 'prize_winning_tickets';

    protected $fillable = [
        'competition_id',
        'prize_id',
        'ticket_id',
        'winning_method',
        'status',
        'winner_user_id',
        'order_id',
        'won_at',
        'claimed_at',
        'fulfilled_at',
        'notes',
    ];

    protected $casts = [
        'won_at' => 'datetime',
        'claimed_at' => 'datetime',
        'fulfilled_at' => 'datetime',
    ];
}
