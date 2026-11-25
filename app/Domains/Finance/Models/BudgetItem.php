<?php

namespace App\Domains\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'budget_allocated' => 'float',
        'actual_expenditure' => 'float',
    ];

    public function financialBudget(): BelongsTo
    {
        return $this->belongsTo(FinancialBudget::class);
    }
}
