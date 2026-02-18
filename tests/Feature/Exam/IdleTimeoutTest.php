<?php

namespace Tests\Feature\Exam;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\User;
use App\Services\ExamParticipationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class IdleTimeoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_expire_attempts_command_marks_idle_attempt_and_writes_audit(): void
    {
        Carbon::setTestNow(now());
        config()->set('security.attempt_idle_timeout_minutes', 1);
        $this->assertSame(1, (int) config('security.attempt_idle_timeout_minutes'));

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $peserta = User::factory()->create(['role' => User::ROLE_PESERTA]);

        $exam = Exam::create([
            'title' => 'Idle Timeout Exam',
            'start_at' => now()->subMinutes(30),
            'end_at' => now()->addMinutes(30),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_RUNNING,
            'created_by' => $admin->id,
        ]);

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $peserta->id,
            'status' => ExamAttempt::STATUS_ACTIVE,
            'started_at' => now()->subMinutes(1),
        ]);

        Carbon::setTestNow(now()->addMinutes(5));

        $freshAttempt = $attempt->fresh(['exam']);
        $this->assertTrue($freshAttempt->updated_at->lt(now()));

        $participationService = app(ExamParticipationService::class);
        $this->assertSame(
            'idle_timeout',
            $participationService->getAttemptExpirationReason($freshAttempt)
        );

        $this->artisan('exams:expire-attempts')->assertExitCode(0);

        $this->assertNotSame(ExamAttempt::STATUS_ACTIVE, $attempt->fresh()->status);
        $this->assertDatabaseHas('security_audits', [
            'action' => 'attempt_idle_timeout',
            'target_type' => 'ExamAttempt',
            'target_id' => $attempt->id,
            'user_agent' => 'system/scheduler',
        ]);
    }
}
