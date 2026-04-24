<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SchoolYear extends Model
{
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class);
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class, 'school_year_id');
    }

    public function semesters(): HasMany
    {
        return $this->hasMany(Semester::class)->orderBy('semester_number');
    }

    public function activeSemester(): HasOne
    {
        return $this->hasOne(Semester::class)->where('is_active', true);
    }
}
