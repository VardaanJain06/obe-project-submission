<?php

namespace App\Domains\Academic\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domains\Faculty\Models\FacultyProfile;

class Department extends Model
{
    protected $guarded = [];

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }

    public function facultyProfiles(): HasMany
    {
        return $this->hasMany(FacultyProfile::class);
    }
}
