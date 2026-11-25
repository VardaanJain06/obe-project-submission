<?php

namespace App\Domains\Academic\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicIntake extends Model
{
    protected $guarded = [];

    protected $casts = [
        'ug_1st_year' => 'integer',
        'ug_2nd_year' => 'integer',
        'ug_3rd_year' => 'integer',
        'ug_4th_year' => 'integer',
        'lateral_entry' => 'integer',
        'total_students' => 'integer',
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
