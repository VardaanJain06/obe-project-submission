<?php

namespace App\Domains\SAR\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SarAuditLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
    ];

    public function sarReport(): BelongsTo
    {
        return $this->belongsTo(SarReport::class);
    }
}
