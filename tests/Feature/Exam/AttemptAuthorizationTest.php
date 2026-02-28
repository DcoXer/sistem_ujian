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

class AttemptAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_peserta_cannot_access_other_users_attempt(): void
    {
        Carbon::setTestNow(now());

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $owner = User::factory()->create(['role' => User::ROLE_PESERTA]);
        $otherPeserta = User::factory()->create(['role' => User::ROLE_PESERTA]);

        $exam = Exam::create([
            'title' => 'Attempt Authorization Exam',
            'start_at' => now()->subMinutes(5),
            'end_at' => now()->addMinutes(30),
            'duration_minutes' => 30,
            'status' => Exam::STATUS_RUNNING,
            'created_by' => $admin->id,
        ]);

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $owner->id,
            'status' => ExamAttempt::STATUS_ACTIVE,
            'started_at' => now()->subMinute(),
        ]);

        $this->actingAs($otherPeserta)
            ->get(route('peserta.exams.show', $attempt))
            ->assertForbidden();

        $this->actingAs($otherPeserta)
            ->post(route('peserta.exams.submit', $attempt))
            ->assertForbidden();
    }

    public function test_peserta_cannot_inject_question_from_different_exam(): void
    {
        Carbon::setTestNow(now());

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $peserta = User::factory()->create(['role' => User::ROLE_PESERTA]);

        $examA = Exam::create([
            'title' => 'Exam A',
            'start_at' => now()->subMinutes(5),
            'end_at' => now()->addMinutes(30),
            'duration_minutes' => 30,
            'status' => Exam::STATUS_RUNNING,
            'created_by' => $admin->id,
        ]);

        $examB = Exam::create([
            'title' => 'Exam B',
            'start_at' => now()->subMinutes(5),
            'end_at' => now()->addMinutes(30),
            'duration_minutes' => 30,
            'status' => Exam::STATUS_RUNNING,
            'created_by' => $admin->id,
        ]);

        $questionB = ExamQuestion::create([
            'exam_id' => $examB->id,
            'question_text' => 'Foreign question',
            'points' => 10,
            'order' => 1,
        ]);

        $optionB = ExamOption::create([
            'exam_question_id' => $questionB->id,
            'option_text' => 'Foreign option',
            'is_correct' => true,
        ]);

        $attemptA = ExamAttempt::create([
            'exam_id' => $examA->id,
            'user_id' => $peserta->id,
            'status' => ExamAttempt::STATUS_ACTIVE,
            'started_at' => now()->subMinute(),
        ]);

        $this->actingAs($peserta)
            ->post(route('peserta.exams.answer', $attemptA), [
                'question_id' => $questionB->id,
                'option_id' => $optionB->id,
            ])
            ->assertStatus(409);

        $this->assertDatabaseMissing('exam_answers', [
            'exam_attempt_id' => $attemptA->id,
            'exam_question_id' => $questionB->id,
        ]);
    }

    public function test_peserta_cannot_inject_option_that_does_not_belong_to_question(): void
    {
        Carbon::setTestNow(now());

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $peserta = User::factory()->create(['role' => User::ROLE_PESERTA]);

        $exam = Exam::create([
            'title' => 'Option Scope Exam',
            'start_at' => now()->subMinutes(5),
            'end_at' => now()->addMinutes(30),
            'duration_minutes' => 30,
            'status' => Exam::STATUS_RUNNING,
            'created_by' => $admin->id,
        ]);

        $question1 = ExamQuestion::create([
            'exam_id' => $exam->id,
            'question_text' => 'Question 1',
            'points' => 10,
            'order' => 1,
        ]);

        $question2 = ExamQuestion::create([
            'exam_id' => $exam->id,
            'question_text' => 'Question 2',
            'points' => 10,
            'order' => 2,
        ]);

        $foreignOption = ExamOption::create([
            'exam_question_id' => $question2->id,
            'option_text' => 'Foreign',
            'is_correct' => false,
        ]);

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $peserta->id,
            'status' => ExamAttempt::STATUS_ACTIVE,
            'started_at' => now()->subMinute(),
        ]);

        $this->actingAs($peserta)
            ->post(route('peserta.exams.answer', $attempt), [
                'question_id' => $question1->id,
                'option_id' => $foreignOption->id,
            ])
            ->assertStatus(409);

        $this->assertDatabaseMissing('exam_answers', [
            'exam_attempt_id' => $attempt->id,
            'exam_question_id' => $question1->id,
            'exam_option_id' => $foreignOption->id,
        ]);
    }

    public function test_peserta_answer_autosave_uses_occ_version_and_rejects_stale_write(): void
    {
        Carbon::setTestNow(now());

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $peserta = User::factory()->create(['role' => User::ROLE_PESERTA]);

        $exam = Exam::create([
            'title' => 'OCC Exam',
            'start_at' => now()->subMinutes(5),
            'end_at' => now()->addMinutes(30),
            'duration_minutes' => 30,
            'status' => Exam::STATUS_RUNNING,
            'created_by' => $admin->id,
        ]);

        $question = ExamQuestion::create([
            'exam_id' => $exam->id,
            'question_text' => 'Question OCC',
            'points' => 10,
            'order' => 1,
        ]);

        $firstOption = ExamOption::create([
            'exam_question_id' => $question->id,
            'option_text' => 'A',
            'is_correct' => false,
        ]);

        $secondOption = ExamOption::create([
            'exam_question_id' => $question->id,
            'option_text' => 'B',
            'is_correct' => true,
        ]);

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $peserta->id,
            'status' => ExamAttempt::STATUS_ACTIVE,
            'started_at' => now()->subMinute(),
        ]);

        $answer = ExamAnswer::create([
            'exam_attempt_id' => $attempt->id,
            'exam_question_id' => $question->id,
            'exam_option_id' => $firstOption->id,
        ]);

        $staleVersion = $answer->updated_at->copy()->subSecond()->toIso8601String();

        $this->actingAs($peserta)
            ->postJson(route('peserta.exams.answer', $attempt), [
                'question_id' => $question->id,
                'option_id' => $secondOption->id,
                'answer_version' => $staleVersion,
            ])
            ->assertStatus(409);

        $this->assertSame(
            $firstOption->id,
            (int) $answer->fresh()->exam_option_id
        );
    }
}
