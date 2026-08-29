<?php

namespace App\Models;

use App\Tenancy\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamTerm extends Model
{
    use HasFactory, TenantScoped;

    protected $fillable = [
        'tenant_id',
        'name',
        'academic_year',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class, 'exam_term_id');
    }

    public function reportCards(): HasMany
    {
        return $this->hasMany(ReportCard::class, 'exam_term_id');
    }
}
