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
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'authoring_start_at' => 'datetime',
            'authoring_end_at' => 'datetime',
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
}
