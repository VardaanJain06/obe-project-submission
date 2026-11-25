<?php

namespace App\Domains\Attainment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\Curriculum\Models\CourseOutcome;
use App\Domains\Academic\Models\AcademicYear;

class AttainmentResult extends Model
{
    protected $guarded = [];

    protected $casts = [
        'direct_cie_score' => 'float',
        'direct_see_score' => 'float',
        'direct_attainment' => 'float',
        'indirect_attainment' => 'float',
        'final_score' => 'float',
    ];

    public function courseOutcome(): BelongsTo
    {
        return $this->belongsTo(CourseOutcome::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
