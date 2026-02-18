<?php

namespace App\Console\Commands;

use App\Models\ExamAttempt;
use App\Services\ExamParticipationService;
use App\Services\SecurityAuditService;
use Illuminate\Console\Command;

class ExpireActiveAttempts extends Command
{
    protected $signature = 'exams:expire-attempts';

    protected $description = 'Expire active exam attempts whose time window has ended.';

    public function handle(ExamParticipationService $participationService, SecurityAuditService $securityAuditService): int
    {
        $expiredCount = 0;
        $idleTimeoutCount = 0;

        ExamAttempt::query()
            ->where('status', ExamAttempt::STATUS_ACTIVE)
            ->with('exam')
            ->chunkById(100, function ($attempts) use ($participationService, $securityAuditService, &$expiredCount, &$idleTimeoutCount) {
                foreach ($attempts as $attempt) {
                    $reason = $participationService->getAttemptExpirationReason($attempt);
                    if (! $reason) {
                        continue;
                    }

                    $participationService->expireAttempt($attempt);
                    $expiredCount++;

                    if ($reason === 'idle_timeout') {
                        $idleTimeoutCount++;
                        $securityAuditService->logSystem('attempt_idle_timeout', $attempt, [
                            'exam_id' => $attempt->exam_id,
                            'idle_timeout_minutes' => (int) config('security.attempt_idle_timeout_minutes', 0),
                        ]);
                    }
                }
            });

        $this->info("{$expiredCount} attempts expired. Idle timeouts: {$idleTimeoutCount}.");

        return 0;
    }
}
