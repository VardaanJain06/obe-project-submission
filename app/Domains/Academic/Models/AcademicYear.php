<?php

namespace App\Domains\Academic\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domains\Faculty\Models\FacultyAcademicYear;
use App\Domains\Finance\Models\FinancialBudget;

class AcademicYear extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_current' => 'boolean',
        'start_year' => 'integer',
        'end_year' => 'integer',
    ];

    public function academicIntakes(): HasMany
    {
        return $this->hasMany(AcademicIntake::class);
    }

    public function facultyAcademicYears(): HasMany
    {
        return $this->hasMany(FacultyAcademicYear::class);
    }

    public function financialBudgets(): HasMany
    {
        return $this->hasMany(FinancialBudget::class);
    }
}
