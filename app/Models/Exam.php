<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_RUNNING = 'running';

    public const STATUS_FINISHED = 'finished';

    public const TYPE_UH = 'UH';

    public const TYPE_UTS = 'UTS';

    public const TYPE_UAS = 'UAS';

    public const TYPE_PAT = 'PAT';

    public const EXAM_TYPES = [self::TYPE_UH, self::TYPE_UTS, self::TYPE_UAS, self::TYPE_PAT];

    protected $fillable = [
        'title',
        'start_at',
        'end_at',
        'authoring_start_at',
        'authoring_end_at',
        'duration_minutes',
        'status',
        'created_by',
        'author_id',
        'teacher_id',
        'subject_id',
        'class_id',
        'target_grade_level',
        'school_class_id',
        'school_year_id',
        'semester_id',
        'exam_type',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'authoring_start_at' => 'datetime',
            'authoring_end_at' => 'datetime',
            'target_grade_level' => 'integer',
        ];
    }

    public function isWithinAuthoringWindow(?\Illuminate\Support\Carbon $at = null): bool
    {
        $at ??= now();

        if ($this->authoring_start_at && $this->authoring_end_at) {
            return $at->betweenIncluded($this->authoring_start_at, $this->authoring_end_at);
        }

        return $this->end_at !== null && $at->lessThanOrEqualTo($this->end_at);
    }

    public function isAuthoringWindowClosed(?\Illuminate\Support\Carbon $at = null): bool
    {
        $at ??= now();

        $windowEnd = $this->authoring_end_at ?? $this->end_at;
        if (! $windowEnd) {
            return false;
        }

        return $at->greaterThanOrEqualTo($windowEnd);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class, 'school_year_id');
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class)->orderBy('order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function canTransitionTo(string $targetStatus): bool
    {
        return match ($this->status) {
            self::STATUS_DRAFT => $targetStatus === self::STATUS_RUNNING,
            self::STATUS_RUNNING => $targetStatus === self::STATUS_FINISHED,
            self::STATUS_FINISHED => false,
            default => false,
        };
    }

    public function isEligibleForPeserta(User $user): bool
    {
        if ($user->role !== User::ROLE_STUDENT) {
            return false;
        }

        $user->loadMissing('schoolClass');

        if ($this->class_id !== null && (int) $this->class_id !== (int) ($user->class_id ?? 0)) {
            return false;
        }

        $participantGradeLevel = (int) ($user->schoolClass?->grade_level ?? 0);
        if ($this->target_grade_level !== null && (int) $this->target_grade_level !== $participantGradeLevel) {
            return false;
        }

        $participantSchoolYearId = (int) ($user->schoolClass?->school_year_id ?? 0);
        if ($this->school_year_id !== null && (int) $this->school_year_id !== $participantSchoolYearId) {
            return false;
        }

        return true;
    }
}
