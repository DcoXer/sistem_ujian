<?php

namespace Tests\Feature\Exam;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamOption;
use App\Models\ExamQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SystemInvariantsTest extends TestCase
{
    use RefreshDatabase;

    public function test_finished_exam_has_no_active_attempt_after_lifecycle_sync(): void
    {
        Carbon::setTestNow(now());

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $peserta = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $exam = Exam::create([
            'title' => 'Invariant Exam',
            'start_at' => now()->subHours(2),
            'end_at' => now()->subHour(),
            'duration_minutes' => 30,
            'status' => Exam::STATUS_FINISHED,
            'created_by' => $admin->id,
        ]);

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $peserta->id,
            'status' => ExamAttempt::STATUS_ACTIVE,
            'started_at' => now()->subHours(2),
        ]);

        $this->artisan('exams:expire-attempts')->assertExitCode(0);

        $this->assertNotSame(ExamAttempt::STATUS_ACTIVE, $attempt->fresh()->status);
    }

    public function test_submitted_attempt_is_immutable_for_answer_and_resubmit(): void
    {
        Carbon::setTestNow(now());

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $peserta = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $exam = Exam::create([
            'title' => 'Immutable Attempt Exam',
            'start_at' => now()->subHour(),
            'end_at' => now()->addHour(),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_RUNNING,
            'created_by' => $admin->id,
        ]);

        $question = ExamQuestion::create([
            'exam_id' => $exam->id,
            'question_text' => '2 + 3 = ?',
            'points' => 10,
            'order' => 1,
        ]);

        $option = ExamOption::create([
            'exam_question_id' => $question->id,
            'option_text' => '5',
            'is_correct' => true,
        ]);

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $peserta->id,
            'status' => ExamAttempt::STATUS_SUBMITTED,
            'started_at' => now()->subMinutes(20),
            'submitted_at' => now()->subMinutes(10),
            'answers_locked_at' => now()->subMinutes(10),
        ]);

        $this->actingAs($peserta)
            ->post(route('student.exams.answer', $attempt), [
                'question_id' => $question->id,
                'option_id' => $option->id,
            ])
            ->assertStatus(409);

        $this->actingAs($peserta)
            ->post(route('student.exams.submit', $attempt))
            ->assertStatus(409);
    }
}


