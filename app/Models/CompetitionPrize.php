<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompetitionPrize extends Model
{
    protected $fillable = [
        'competition_id',
        'name',
        'description',
        'prize_role',
        'prize_type',
        'image_path',
        'prize_value',
        'currency',
        'quantity',
        'winning_method',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'prize_value' => 'decimal:2',
            'quantity' => 'integer',
            'sort_order' => 'integer',
        ];
    }
}
