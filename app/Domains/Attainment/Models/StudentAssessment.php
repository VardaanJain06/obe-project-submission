<?php

namespace App\Domains\Attainment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\Curriculum\Models\Course;
use App\Domains\Curriculum\Models\CourseOutcome;
use App\Domains\Academic\Models\AcademicYear;

class StudentAssessment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'total_students_attempted' => 'integer',
        'students_meeting_target' => 'integer',
        'percentage_achieved' => 'float',
        'attainment_level' => 'float',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function courseOutcome(): BelongsTo
    {
        return $this->belongsTo(CourseOutcome::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
