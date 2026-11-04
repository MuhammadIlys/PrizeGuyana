<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetitionTermsVersion extends Model
{
    use HasFactory;

    protected $table = 'competition_terms_versions';

    protected $fillable = [
        'competition_id',
        'version',
        'title',
        'content',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function terms()
{
    return $this->belongsTo(
        CompetitionTermsVersion::class,
        'terms_id'
    );
}


}