<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Domains\Academic\Models\Department;
use App\Domains\Faculty\Models\FacultyProfile;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_SUPER_ADMIN = 'Super Admin';
    public const ROLE_HOD = 'HOD';
    public const ROLE_FACULTY = 'Faculty';
    public const ROLE_IQAC = 'IQAC Admin';
    public const ROLE_EVALUATOR = 'Evaluator';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'department_id',
        'designation',
        'faculty_code',
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function facultyProfile(): HasOne
    {
        return $this->hasOne(FacultyProfile::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isHod(): bool
    {
        return $this->role === self::ROLE_HOD || $this->isSuperAdmin();
    }

    public function isFaculty(): bool
    {
        return $this->role === self::ROLE_FACULTY || $this->isHod() || $this->isSuperAdmin();
    }

    public function isIqac(): bool
    {
        return $this->role === self::ROLE_IQAC || $this->isSuperAdmin();
    }

    public function isEvaluator(): bool
    {
        return $this->role === self::ROLE_EVALUATOR || $this->isSuperAdmin();
    }
}
