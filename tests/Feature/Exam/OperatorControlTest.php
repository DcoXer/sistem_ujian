<?php

namespace Tests\Feature\Exam;

use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\ExamOption;
use App\Models\ExamQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OperatorControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_can_force_submit_active_attempt(): void
    {
        Carbon::setTestNow(now());

        $operator = User::factory()->create(['role' => User::ROLE_OPERATOR]);
        $peserta = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $exam = Exam::create([
            'title' => 'Exam Operator Control',
            'start_at' => now()->subHour(),
            'end_at' => now()->addHour(),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_RUNNING,
            'created_by' => User::factory()->create(['role' => User::ROLE_ADMIN])->id,
        ]);

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $peserta->id,
            'status' => ExamAttempt::STATUS_ACTIVE,
            'started_at' => now()->subMinutes(10),
        ]);

        $this->actingAs($operator)
            ->post(route('operator.exams.force-submit', $attempt), [
                'reason' => 'Peserta disconnect saat ujian.',
            ])
            ->assertSessionHasNoErrors();

        $fresh = $attempt->fresh();
        $this->assertSame(ExamAttempt::STATUS_FINISHED, $fresh->status);
        $this->assertNotNull($fresh->submitted_at);
        $this->assertNotNull($fresh->scoring_processed_at);
        $this->assertDatabaseHas('exam_attempt_audits', [
            'exam_attempt_id' => $attempt->id,
            'actor_user_id' => $operator->id,
            'action' => 'force_submit',
        ]);
    }

    public function test_operator_cannot_reopen_attempt_when_exam_not_running(): void
    {
        Carbon::setTestNow(now());

        $operator = User::factory()->create(['role' => User::ROLE_OPERATOR]);
        $peserta = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $exam = Exam::create([
            'title' => 'Exam Finished',
            'start_at' => now()->subHours(2),
            'end_at' => now()->subHour(),
            'duration_minutes' => 45,
            'status' => Exam::STATUS_FINISHED,
            'created_by' => User::factory()->create(['role' => User::ROLE_ADMIN])->id,
        ]);

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $peserta->id,
            'status' => ExamAttempt::STATUS_FINISHED,
            'started_at' => now()->subHours(2),
            'submitted_at' => now()->subHour(),
            'scoring_processed_at' => now()->subHour(),
            'score' => 50,
        ]);

        $this->actingAs($operator)
            ->from(route('operator.exams.show', $exam))
            ->post(route('operator.exams.reopen', $attempt), [
                'reason' => 'Permintaan teknis dari peserta.',
            ])
            ->assertStatus(409);

        $this->assertSame(ExamAttempt::STATUS_FINISHED, $attempt->fresh()->status);
    }

    public function test_operator_cannot_override_auto_score_for_objective_attempt(): void
    {
        Carbon::setTestNow(now());

        $operator = User::factory()->create(['role' => User::ROLE_OPERATOR]);
        $peserta = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $exam = Exam::create([
            'title' => 'Objective Exam',
            'start_at' => now()->subHour(),
            'end_at' => now()->addHour(),
            'duration_minutes' => 30,
            'status' => Exam::STATUS_RUNNING,
            'created_by' => User::factory()->create(['role' => User::ROLE_ADMIN])->id,
        ]);

        $question = ExamQuestion::create([
            'exam_id' => $exam->id,
            'question_text' => '2 + 2 = ?',
            'points' => 10,
            'order' => 1,
        ]);

        $option = ExamOption::create([
            'exam_question_id' => $question->id,
            'option_text' => '4',
            'is_correct' => true,
        ]);

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $peserta->id,
            'status' => ExamAttempt::STATUS_FINISHED,
            'started_at' => now()->subMinutes(20),
            'submitted_at' => now()->subMinutes(10),
            'answers_locked_at' => now()->subMinutes(10),
            'scoring_processed_at' => now()->subMinutes(10),
            'score' => 10,
        ]);

        ExamAnswer::create([
            'exam_attempt_id' => $attempt->id,
            'exam_question_id' => $question->id,
            'exam_option_id' => $option->id,
            'locked_at' => now()->subMinutes(10),
        ]);

        $this->actingAs($operator)
            ->from(route('operator.exams.show', $exam))
            ->post(route('operator.exams.manual-score', $attempt), [
                'intent' => 'manual_essay_scoring',
                'score' => 0,
                'reason' => 'Coba override nilai otomatis.',
            ])
            ->assertStatus(409);
    }

    public function test_operator_index_shows_running_exam_even_if_not_started_yet(): void
    {
        Carbon::setTestNow(now());

        $operator = User::factory()->create(['role' => User::ROLE_OPERATOR]);
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Exam::create([
            'title' => 'Future Running Exam',
            'start_at' => now()->addHour(),
            'end_at' => now()->addHours(2),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_RUNNING,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($operator)
            ->get(route('operator.exams.index'))
            ->assertOk()
            ->assertSee('Future Running Exam');
    }

    public function test_manual_score_route_rejects_missing_intent(): void
    {
        Carbon::setTestNow(now());

        $operator = User::factory()->create(['role' => User::ROLE_OPERATOR]);
        $peserta = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $exam = Exam::create([
            'title' => 'Intent Guard Exam',
            'start_at' => now()->subHour(),
            'end_at' => now()->addHour(),
            'duration_minutes' => 30,
            'status' => Exam::STATUS_RUNNING,
            'created_by' => $admin->id,
        ]);

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $peserta->id,
            'status' => ExamAttempt::STATUS_SUBMITTED,
            'started_at' => now()->subMinutes(20),
            'submitted_at' => now()->subMinutes(10),
        ]);

        $this->actingAs($operator)
            ->from(route('operator.exams.show', $exam))
            ->post(route('operator.exams.manual-score', $attempt), [
                'score' => 80,
                'reason' => 'Essay correction from operator.',
            ])
            ->assertSessionHasErrors('manual_score');
    }

    public function test_operator_cannot_publish_exam(): void
    {
        Carbon::setTestNow(now());

        $operator = User::factory()->create(['role' => User::ROLE_OPERATOR]);
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $exam = Exam::create([
            'title' => 'Draft Locked For Operator',
            'start_at' => now()->addHour(),
            'end_at' => now()->addHours(2),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_DRAFT,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($operator)
            ->post(route('admin.exams.publish', $exam))
            ->assertForbidden();

        $this->assertSame(Exam::STATUS_DRAFT, $exam->fresh()->status);
    }

    public function test_operator_reopen_is_rejected_on_second_replay_request(): void
    {
        Carbon::setTestNow(now());

        $operator = User::factory()->create(['role' => User::ROLE_OPERATOR]);
        $peserta = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $exam = Exam::create([
            'title' => 'Replay Reopen Guard',
            'start_at' => now()->subHour(),
            'end_at' => now()->addHour(),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_RUNNING,
            'created_by' => $admin->id,
        ]);

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $peserta->id,
            'status' => ExamAttempt::STATUS_FINISHED,
            'started_at' => now()->subMinutes(20),
            'submitted_at' => now()->subMinutes(5),
            'scoring_processed_at' => now()->subMinutes(5),
        ]);

        $this->actingAs($operator)
            ->post(route('operator.exams.reopen', $attempt), [
                'reason' => 'Incident teknis valid.',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(ExamAttempt::STATUS_ACTIVE, $attempt->fresh()->status);

        $this->actingAs($operator)
            ->post(route('operator.exams.reopen', $attempt), [
                'reason' => 'Replay request reopen.',
            ])
            ->assertStatus(409);

        $this->assertSame(ExamAttempt::STATUS_ACTIVE, $attempt->fresh()->status);
    }
}

