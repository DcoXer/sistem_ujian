<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamAnswer extends Model
{
    protected $fillable = [
        'exam_attempt_id',
        'exam_question_id',
        'exam_option_id',
        'answer_text',
        'locked_at',
    ];

    protected function casts(): array
    {
        return [
            'locked_at' => 'datetime',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class, 'exam_attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(ExamQuestion::class, 'exam_question_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(ExamOption::class, 'exam_option_id');
    }
}
