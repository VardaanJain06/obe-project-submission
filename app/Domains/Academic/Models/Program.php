<?php

namespace App\Domains\Academic\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domains\Curriculum\Models\Course;
use App\Domains\Curriculum\Models\ProgramOutcome;
use App\Domains\Curriculum\Models\ProgramSpecificOutcome;
use App\Domains\Student\Models\StudentCohort;
use App\Domains\SAR\Models\SarReport;

class Program extends Model
{
    protected $guarded = [];

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function academicIntakes(): HasMany
    {
        return $this->hasMany(AcademicIntake::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function programOutcomes(): HasMany
    {
        return $this->hasMany(ProgramOutcome::class);
    }

    public function programSpecificOutcomes(): HasMany
    {
        return $this->hasMany(ProgramSpecificOutcome::class);
    }

    public function studentCohorts(): HasMany
    {
        return $this->hasMany(StudentCohort::class);
    }

    public function sarReports(): HasMany
    {
        return $this->hasMany(SarReport::class);
    }
}
