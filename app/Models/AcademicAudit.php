<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicAudit extends Model
{
    protected $fillable = [
        'title',
        'audit_type',
        'status',
        'audit_date',
        'findings',
        'recommendations',
        'auditor_name',
    ];

    protected $casts = [
        'audit_date' => 'date',
    ];
}