<?php

namespace App\Domains\Faculty\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domains\Academic\Models\Department;

class FacultyProfile extends Model
{
    protected $guarded = [];

    protected $casts = [
        'joining_date' => 'date',
        'is_active' => 'boolean',
        'teaching_experience_years' => 'integer',
        'industry_experience_years' => 'integer',
        'year_of_highest_degree' => 'integer',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function academicYears(): HasMany
    {
        return $this->hasMany(FacultyAcademicYear::class);
    }

    public function fdps(): HasMany
    {
        return $this->hasMany(FacultyFdp::class);
    }

    public function publications(): HasMany
    {
        return $this->hasMany(FacultyPublication::class);
    }
}
