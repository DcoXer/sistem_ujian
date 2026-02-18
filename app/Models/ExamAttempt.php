<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamAttempt extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_FINISHED = 'finished';

    protected $fillable = [
        'exam_id',
        'user_id',
        'status',
        'started_at',
        'submitted_at',
        'answers_locked_at',
        'scoring_processed_at',
        'score',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'answers_locked_at' => 'datetime',
            'scoring_processed_at' => 'datetime',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ExamAnswer::class);
    }

    public function audits(): HasMany
    {
        return $this->hasMany(ExamAttemptAudit::class);
    }

    public function canTransitionTo(string $targetStatus): bool
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => $targetStatus === self::STATUS_SUBMITTED,
            self::STATUS_SUBMITTED => $targetStatus === self::STATUS_FINISHED,
            self::STATUS_FINISHED => false,
            default => false,
        };
    }
}
