<?php

namespace App\Services;

use App\Models\Exam;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ExamContentCacheService
{
    protected ?bool $hasQuestionImageColumn = null;

    public function getExamContent(int $examId): array
    {
        $store = $this->store();
        $cacheKey = $this->cacheKey($examId);
        $ttl = now()->addSeconds((int) config('exam.content_cache_ttl_seconds', 1800));

        $cached = $store->get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $resolver = fn (): array => $this->buildExamContent($examId);

        if (! $this->supportsLocks($store)) {
            $payload = $resolver();
            $store->put($cacheKey, $payload, $ttl);

            return $payload;
        }

        $lockSeconds = max(1, (int) config('exam.content_cache_lock_seconds', 10));
        $lockWaitSeconds = max(1, (int) config('exam.content_cache_lock_wait_seconds', 3));
        $fallbackWaitMs = max(0, (int) config('exam.content_cache_fallback_wait_ms', 1200));

        try {
            return $store->lock($this->lockKey($examId), $lockSeconds)->block($lockWaitSeconds, function () use ($store, $cacheKey, $ttl, $resolver): array {
                $cachedAfterLock = $store->get($cacheKey);
                if (is_array($cachedAfterLock)) {
                    return $cachedAfterLock;
                }

                $payload = $resolver();
                $store->put($cacheKey, $payload, $ttl);

                return $payload;
            });
        } catch (LockTimeoutException) {
            $waited = 0;
            while ($waited < $fallbackWaitMs) {
                usleep(100000);
                $waited += 100;
                $cachedAfterWait = $store->get($cacheKey);
                if (is_array($cachedAfterWait)) {
                    return $cachedAfterWait;
                }
            }

            $payload = $resolver();
            $store->put($cacheKey, $payload, $ttl);

            return $payload;
        }
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
        return 'exam:content:v2:'.$examId;
    }

    protected function lockKey(int $examId): string
    {
        return $this->cacheKey($examId).':build-lock';
    }

    protected function buildExamContent(int $examId): array
    {
        $exam = Exam::query()
            ->select(['id', 'title'])
            ->with([
                'questions' => fn ($query) => $query->select($this->questionSelectColumns()),
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
                    'question_image_url' => $this->hasQuestionImageColumn() && $question->question_image_path
                        ? Storage::disk('public')->url($question->question_image_path)
                        : null,
                    'points' => (int) $question->points,
                    'options' => $question->options->map(fn ($option) => [
                        'id' => (int) $option->id,
                        'option_text' => $option->option_text,
                    ])->values()->all(),
                ];
            })->values()->all(),
        ];
    }

    protected function store(): CacheRepository
    {
        $store = config('exam.content_cache_store');

        return $store ? Cache::store($store) : Cache::store();
    }

    protected function supportsLocks(CacheRepository $store): bool
    {
        return method_exists($store->getStore(), 'lock');
    }

    protected function questionSelectColumns(): array
    {
        $columns = ['id', 'exam_id', 'question_text', 'points', 'order'];
        if ($this->hasQuestionImageColumn()) {
            $columns[] = 'question_image_path';
        }

        return $columns;
    }

    protected function hasQuestionImageColumn(): bool
    {
        if ($this->hasQuestionImageColumn !== null) {
            return $this->hasQuestionImageColumn;
        }

        return $this->hasQuestionImageColumn = Schema::hasColumn('exam_questions', 'question_image_path');
    }
}
