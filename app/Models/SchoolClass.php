<?php

namespace App\Models;

use App\Tenancy\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    use HasFactory, TenantScoped;

    protected $table = 'school_classes';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'order_index',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class, 'class_id')->orderBy('name');
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class, 'class_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'class_id');
    }
}
