<?php

namespace App\Domains\Student\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\Academic\Models\Program;
use App\Domains\Academic\Models\AcademicYear;

class StudentPlacement extends Model
{
    protected $guarded = [];

    protected $casts = [
        'final_year_students' => 'integer',
        'placed_students' => 'integer',
        'higher_studies_students' => 'integer',
        'entrepreneurs_count' => 'integer',
        'median_salary_lpa' => 'float',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
