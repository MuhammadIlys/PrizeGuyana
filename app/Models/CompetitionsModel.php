<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CompetitionsModel extends Model
{
    use HasFactory;

    protected $table = 'my_competitions';

    protected $fillable = [
        'title',
        'slug',
        'internal_reference',
        'short_description',
        'description',
        'meta_title',
        'meta_description',
        'category_id',
        'is_featured',
        'main_image',
        'video_path',
        'starts_at',
        'ends_at',
        'draw_at',
        'entry_price',
        // 'currency',
        'total_entries',
        'max_entries_per_customer',
        'entry_allocation_method',

        'qualification_enabled',
        'qualification_question_ids',
        
        'instant_win_enabled',
        'draw_method',
        'minimum_age',
        'terms_id',
        'status',
        'published_at',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'qualification_enabled' => 'boolean',
        'instant_win_enabled' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'draw_at' => 'datetime',
        'published_at' => 'datetime',
        'entry_price' => 'decimal:2',
        'qualification_question_ids' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (CompetitionsModel $competition) {
            if (empty($competition->slug)) {
                $competition->slug = Str::slug($competition->title);
            }
        });
    }
}
