<?php

namespace App\Services;

use App\Models\Exam;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class ExamLifecycleService
{
    public function __construct(
        protected ExamConstraintService $constraintService
    ) {
    }

    /**
     * Lifecycle authority service for exam status only.
     * Dependency direction:
     * - must not depend on other domain services.
     * - may be called by controllers/commands as orchestration entrypoint.
     */
    /**
     * Scheduler authority path. Request paths may call this as fallback.
     */
    public function closeExpiredExams(): int
    {
        return Exam::query()
            ->where('status', Exam::STATUS_RUNNING)
            ->where('end_at', '<', now())
            ->update(['status' => Exam::STATUS_FINISHED]);
    }

    /**
     * Publish exam via single lifecycle choke-point.
     *
     * @throws AuthorizationException
     */
    public function publishDraftExam(Exam $exam): Exam
    {
        return DB::transaction(function () use ($exam) {
            $exam = Exam::query()
                ->with(['questions.options', 'teacher'])
                ->lockForUpdate()
                ->findOrFail($exam->id);

            $this->constraintService->validatePublishability($exam);

            if (! $exam->canTransitionTo(Exam::STATUS_RUNNING)) {
                throw new AuthorizationException('Transisi state ujian tidak valid.');
            }

            $exam->update(['status' => Exam::STATUS_RUNNING]);

            return $exam->fresh();
        });
    }
}
