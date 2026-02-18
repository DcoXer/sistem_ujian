<?php

namespace Tests\Feature\Exam;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ExpirationTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_finishes_expired_exams()
    {
        Carbon::setTestNow(now());

        $exam = Exam::create([
            'title' => 'Short exam',
            'start_at' => now()->subHour(),
            'end_at' => now()->subMinute(),
            'duration_minutes' => 10,
            'status' => Exam::STATUS_RUNNING,
            'created_by' => User::factory()->create()->id,
        ]);

        $this->assertSame(Exam::STATUS_RUNNING, $exam->status);

        $this->artisan('exams:finish-expired')->assertExitCode(0);

        $this->assertSame(Exam::STATUS_FINISHED, $exam->fresh()->status);
    }

    public function test_participant_index_hides_expired_exams_even_if_status_running()
    {
        Carbon::setTestNow(now());

        $user = User::factory()->create();

        $expired = Exam::create([
            'title' => 'Expired exam',
            'start_at' => now()->subHours(2),
            'end_at' => now()->subHour(),
            'duration_minutes' => 30,
            'status' => Exam::STATUS_RUNNING,
            'created_by' => $user->id,
        ]);

        // make sure query would normally include by status
        $this->assertDatabaseHas('exams', ['id' => $expired->id, 'status' => Exam::STATUS_RUNNING]);

        $this->actingAs($user)
            ->get(route('peserta.exams.index'))
            ->assertDontSee('Expired exam');
    }

    public function test_command_expires_active_attempts_after_deadline(): void
    {
        Carbon::setTestNow(now());

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $peserta = User::factory()->create(['role' => User::ROLE_PESERTA]);

        $exam = Exam::create([
            'title' => 'Attempt Expiry Test',
            'start_at' => now()->subHours(2),
            'end_at' => now()->addHour(),
            'duration_minutes' => 30,
            'status' => Exam::STATUS_RUNNING,
            'created_by' => $admin->id,
        ]);

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $peserta->id,
            'status' => ExamAttempt::STATUS_ACTIVE,
            'started_at' => now()->subMinutes(45),
        ]);

        $this->artisan('exams:expire-attempts')->assertExitCode(0);

        $fresh = $attempt->fresh();
        $this->assertSame(ExamAttempt::STATUS_FINISHED, $fresh->status);
        $this->assertNotNull($fresh->submitted_at);
        $this->assertNotNull($fresh->answers_locked_at);
        $this->assertNotNull($fresh->scoring_processed_at);
    }
}
