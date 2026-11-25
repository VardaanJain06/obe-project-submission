<?php

namespace App\Domains\Attainment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\Academic\Models\Program;
use App\Domains\Academic\Models\AcademicYear;

class IndirectAssessment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'average_rating' => 'float',
        'total_respondents' => 'integer',
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
