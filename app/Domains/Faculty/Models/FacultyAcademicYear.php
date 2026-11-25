<?php

namespace App\Domains\Faculty\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\Academic\Models\AcademicYear;
use App\Domains\Academic\Models\Program;

class FacultyAcademicYear extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_exclusive_to_program' => 'boolean',
    ];

    public function facultyProfile(): BelongsTo
    {
        return $this->belongsTo(FacultyProfile::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }
}
