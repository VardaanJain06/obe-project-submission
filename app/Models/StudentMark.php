<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentMark extends Model
{
    protected $guarded = [];

    protected $casts = [
        'marks_obtained' => 'float',
    ];

    public function assessmentQuestion(): BelongsTo
    {
        return $this->belongsTo(AssessmentQuestion::class);
    }
}