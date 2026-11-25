<?php

namespace App\Domains\SAR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SarEvidence extends Model
{
    protected $table = 'sar_evidences';
    protected $guarded = [];

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(SarCriterion::class, 'sar_criterion_id');
    }
}
