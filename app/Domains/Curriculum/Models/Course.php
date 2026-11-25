<?php

namespace App\Domains\Curriculum\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domains\Academic\Models\Program;
use App\Domains\Academic\Models\AcademicYear;
use App\Domains\Attainment\Models\StudentAssessment;

class Course extends Model
{
    protected $guarded = [];

    protected $casts = [
        'semester' => 'integer',
        'credits' => 'float',
        'lecture_hours' => 'integer',
        'tutorial_hours' => 'integer',
        'practical_hours' => 'integer',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function courseOutcomes(): HasMany
    {
        return $this->hasMany(CourseOutcome::class);
    }

    public function studentAssessments(): HasMany
    {
        return $this->hasMany(StudentAssessment::class);
    }
}
