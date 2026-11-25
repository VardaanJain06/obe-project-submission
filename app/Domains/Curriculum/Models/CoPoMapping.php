<?php

namespace App\Domains\Curriculum\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoPoMapping extends Model
{
    protected $guarded = [];

    protected $casts = [
        'correlation_level' => 'integer', // 1, 2, 3
    ];

    public function courseOutcome(): BelongsTo
    {
        return $this->belongsTo(CourseOutcome::class);
    }
}
