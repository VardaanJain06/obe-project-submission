<?php

namespace App\Domains\SAR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domains\Academic\Models\Program;
use App\Domains\Academic\Models\AcademicYear;

class SarReport extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_locked' => 'boolean',
        'submitted_at' => 'datetime',
        'locked_at' => 'datetime',
        'total_claimed_marks' => 'float',
        'evaluated_marks' => 'float',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function criteria(): HasMany
    {
        return $this->hasMany(SarCriterion::class)->orderBy('criterion_number');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(SarAuditLog::class)->orderByDesc('created_at');
    }
}
