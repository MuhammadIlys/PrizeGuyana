<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompetitionCountry extends Model
{
    protected $table = 'competition_country';

    protected $fillable = [
        'competition_id',
        'country_code',
    ];
}
