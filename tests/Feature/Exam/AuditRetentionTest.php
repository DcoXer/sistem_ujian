<?php

namespace Tests\Feature\Exam;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamAttemptAudit;
use App\Models\SecurityAudit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuditRetentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_prune_audits_command_deletes_logs_older_than_retention_window(): void
    {
        Carbon::setTestNow(now());

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $peserta = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $exam = Exam::create([
            'title' => 'Retention Exam',
            'start_at' => now()->subHour(),
            'end_at' => now()->addHour(),
            'duration_minutes' => 30,
            'status' => Exam::STATUS_RUNNING,
            'created_by' => $admin->id,
        ]);
        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $peserta->id,
            'status' => ExamAttempt::STATUS_ACTIVE,
            'started_at' => now()->subMinutes(5),
        ]);

        $oldSecurityAudit = SecurityAudit::query()->create([
            'action' => 'old_security_audit',
        ]);
        DB::table('security_audits')
            ->where('id', $oldSecurityAudit->id)
            ->update([
                'created_at' => now()->subDays(120),
                'updated_at' => now()->subDays(120),
            ]);

        $oldAttemptAudit = ExamAttemptAudit::query()->create([
            'exam_attempt_id' => $attempt->id,
            'actor_user_id' => null,
            'action' => 'old_attempt_audit',
        ]);
        DB::table('exam_attempt_audits')
            ->where('id', $oldAttemptAudit->id)
            ->update([
                'created_at' => now()->subDays(120),
                'updated_at' => now()->subDays(120),
            ]);

        $freshSecurityAudit = SecurityAudit::query()->create([
            'action' => 'fresh_security_audit',
        ]);
        DB::table('security_audits')
            ->where('id', $freshSecurityAudit->id)
            ->update([
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ]);

        $this->artisan('security:prune-audits --days=90')->assertExitCode(0);

        $this->assertDatabaseMissing('security_audits', ['action' => 'old_security_audit']);
        $this->assertDatabaseHas('security_audits', ['action' => 'fresh_security_audit']);
        $this->assertDatabaseMissing('exam_attempt_audits', ['action' => 'old_attempt_audit']);
    }
}

