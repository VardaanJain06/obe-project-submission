<?php

namespace App\Domains\Curriculum\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domains\Academic\Models\Program;

class ProgramSpecificOutcome extends Model
{
    protected $guarded = [];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }
}
