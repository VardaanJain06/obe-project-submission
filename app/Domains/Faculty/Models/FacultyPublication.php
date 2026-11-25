<?php

namespace App\Domains\Faculty\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\Academic\Models\AcademicYear;

class FacultyPublication extends Model
{
    protected $guarded = [];

    protected $casts = [
        'published_date' => 'date',
        'impact_factor' => 'float',
    ];

    public function facultyProfile(): BelongsTo
    {
        return $this->belongsTo(FacultyProfile::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
