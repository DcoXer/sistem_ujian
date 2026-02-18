<?php

namespace App\Services;

use App\Models\ExamAttempt;
use Illuminate\Support\Facades\DB;

class ExamScoringService
{
    /**
     * Scoring primitive service.
     * Dependency direction:
     * - must not depend on orchestration services.
     * - called by participation/operator services after answer lock.
     */
    public function scoreAttempt(ExamAttempt $attempt): ExamAttempt
    {
        return DB::transaction(function () use ($attempt) {
            $attempt = ExamAttempt::with(['exam.questions.options', 'answers'])
                ->lockForUpdate()
                ->findOrFail($attempt->id);

            if ($attempt->scoring_processed_at !== null) {
                return $attempt;
            }

            if (! $attempt->canTransitionTo(ExamAttempt::STATUS_FINISHED)) {
                return $attempt;
            }

            $score = 0;
            $answersByQuestion = $attempt->answers->keyBy('exam_question_id');

            foreach ($attempt->exam->questions as $question) {
                $answer = $answersByQuestion->get($question->id);
                if (! $answer || ! $answer->exam_option_id) {
                    continue;
                }

                $correctOption = $question->options->firstWhere('is_correct', true);
                if ($correctOption && (int) $correctOption->id === (int) $answer->exam_option_id) {
                    $score += (int) $question->points;
                }
            }

            $attempt->update([
                'status' => ExamAttempt::STATUS_FINISHED,
                'score' => $score,
                'scoring_processed_at' => now(),
            ]);

            return $attempt->fresh(['exam', 'answers']);
        });
    }
}
