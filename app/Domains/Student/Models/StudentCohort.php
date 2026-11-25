<?php

namespace App\Domains\Student\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\Academic\Models\Program;

class StudentCohort extends Model
{
    protected $guarded = [];

    protected $casts = [
        'entry_year' => 'integer',
        'graduation_year' => 'integer',
        'sanctioned_intake' => 'integer',
        'admitted_first_year' => 'integer',
        'lateral_admitted_second_year' => 'integer',
        'graduated_without_backlog' => 'integer',
        'graduated_in_stipulated_time' => 'integer',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }
}
