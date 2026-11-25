<?php

namespace App\Domains\Curriculum\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domains\Attainment\Models\StudentAssessment;
use App\Domains\Attainment\Models\AttainmentResult;

class CourseOutcome extends Model
{
    protected $guarded = [];

    protected $casts = [
        'target_percentage' => 'float',
        'target_level' => 'float',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function coPoMappings(): HasMany
    {
        return $this->hasMany(CoPoMapping::class);
    }

    public function studentAssessments(): HasMany
    {
        return $this->hasMany(StudentAssessment::class);
    }

    public function attainmentResults(): HasMany
    {
        return $this->hasMany(AttainmentResult::class);
    }
}
