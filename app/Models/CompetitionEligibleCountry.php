<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class CompetitionEligibleCountry extends Model
{
    use HasFactory;

    protected $table = 'competition_eligible_countries';

    protected $fillable = [
        'competition_id',
        'country_code',
    ];

}
