<?php

namespace App\Domains\SAR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SarCriterion extends Model
{
    protected $guarded = [];

    protected $casts = [
        'criterion_number' => 'integer',
        'max_marks' => 'float',
        'claimed_marks' => 'float',
        'is_verified' => 'boolean',
        'metrics_snapshot' => 'array',
    ];

    public function sarReport(): BelongsTo
    {
        return $this->belongsTo(SarReport::class);
    }

    public function evidences()
    {
        return $this->hasMany(SarEvidence::class, 'sar_criterion_id');
    }
}
