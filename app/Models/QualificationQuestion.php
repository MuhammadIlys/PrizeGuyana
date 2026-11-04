<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class QualificationQuestion extends Model
{
    use HasFactory;

    protected $table = 'qualification_questions';

    protected $fillable = [
        'title',
        'slug',
        'question',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_answer',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (QualificationQuestion $question) {
            if (empty($question->slug)) {
                $question->slug = Str::slug($question->title);
            }
        });
    }
}
