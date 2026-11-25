<?php

namespace App\Domains\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domains\Academic\Models\Institution;
use App\Domains\Academic\Models\Program;
use App\Domains\Academic\Models\AcademicYear;

class FinancialBudget extends Model
{
    protected $guarded = [];

    protected $casts = [
        'total_budget_allocated' => 'float',
        'total_actual_expenditure' => 'float',
        'utilization_percentage' => 'float',
    ];

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BudgetItem::class);
    }
}
