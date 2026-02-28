<?php

namespace App\Services;

use App\Models\Exam;
use Illuminate\Support\Facades\Cache;

class ExamContentCacheService
{
    public function getExamContent(int $examId): array
    {
        return $this->store()->remember(
            $this->cacheKey($examId),
            now()->addSeconds((int) config('exam.content_cache_ttl_seconds', 1800)),
            function () use ($examId): array {
                $exam = Exam::query()
                    ->select(['id', 'title'])
                    ->with([
                        'questions' => fn ($query) => $query->select(['id', 'exam_id', 'question_text', 'points', 'order']),
                        'questions.options' => fn ($query) => $query->select(['id', 'exam_question_id', 'option_text']),
                    ])
                    ->findOrFail($examId);

                return [
                    'id' => $exam->id,
                    'title' => $exam->title,
                    'questions' => $exam->questions->map(function ($question) {
                        return [
                            'id' => (int) $question->id,
                            'order' => (int) $question->order,
                            'question_text' => $question->question_text,
                            'points' => (int) $question->points,
                            'options' => $question->options->map(fn ($option) => [
                                'id' => (int) $option->id,
                                'option_text' => $option->option_text,
                            ])->values()->all(),
                        ];
                    })->values()->all(),
                ];
            }
        );
    }

    public function warmExamContent(int $examId): void
    {
        $this->getExamContent($examId);
    }

    public function invalidateExamContent(int $examId): void
    {
        $this->store()->forget($this->cacheKey($examId));
    }

    public function cacheKey(int $examId): string
    {
        return 'exam:content:v1:'.$examId;
    }

    protected function store()
    {
        $store = config('exam.content_cache_store');

        return $store ? Cache::store($store) : Cache::store();
    }
}
