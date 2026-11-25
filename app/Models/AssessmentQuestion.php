<?php

namespace App\Models;

use App\Domains\Curriculum\Models\CourseOutcome;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentQuestion extends Model
{
    protected $guarded = [];

    protected $casts = [
        'max_marks' => 'float',
        'question_number' => 'integer',
    ];

    public function assessmentBatch(): BelongsTo
    {
        return $this->belongsTo(AssessmentBatch::class);
    }

    public function courseOutcome(): BelongsTo
    {
        return $this->belongsTo(CourseOutcome::class);
    }

    public function studentMarks(): HasMany
    {
        return $this->hasMany(StudentMark::class);
    }
}