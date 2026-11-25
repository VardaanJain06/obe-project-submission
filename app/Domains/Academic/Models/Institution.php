<?php

namespace App\Domains\Academic\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Institution extends Model
{
    protected $guarded = [];

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }
}
