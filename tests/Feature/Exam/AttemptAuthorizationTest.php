<?php

namespace Tests\Feature\Exam;

use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\ExamOption;
use App\Models\ExamQuestion;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
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
        $owner = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $otherPeserta = User::factory()->create(['role' => User::ROLE_STUDENT]);

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
            ->get(route('student.exams.show', $attempt))
            ->assertForbidden();

        $this->actingAs($otherPeserta)
            ->post(route('student.exams.submit', $attempt))
            ->assertForbidden();
    }

    public function test_peserta_cannot_inject_question_from_different_exam(): void
    {
        Carbon::setTestNow(now());

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $peserta = User::factory()->create(['role' => User::ROLE_STUDENT]);

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
            ->post(route('student.exams.answer', $attemptA), [
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
        $peserta = User::factory()->create(['role' => User::ROLE_STUDENT]);

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
            ->post(route('student.exams.answer', $attempt), [
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
        $peserta = User::factory()->create(['role' => User::ROLE_STUDENT]);

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

        $answer->update(['lock_version' => 2]);
        $staleVersion = 1;

        $this->actingAs($peserta)
            ->postJson(route('student.exams.answer', $attempt), [
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

    public function test_peserta_cannot_start_exam_outside_school_domain(): void
    {
        Carbon::setTestNow(now());

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $peserta = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $yearA = SchoolYear::create([
            'name' => '2026/2027',
            'is_active' => true,
        ]);
        $yearB = SchoolYear::create([
            'name' => '2027/2028',
            'is_active' => false,
        ]);

        $classA = SchoolClass::create([
            'name' => 'X-IPA-1',
            'grade_level' => 10,
            'school_year_id' => $yearA->id,
        ]);
        $classB = SchoolClass::create([
            'name' => 'X-IPA-2',
            'grade_level' => 10,
            'school_year_id' => $yearB->id,
        ]);

        $peserta->update([
            'class_id' => $classA->id,
        ]);

        $foreignScopedExam = Exam::create([
            'title' => 'Exam Kelas Lain',
            'start_at' => now()->subMinutes(5),
            'end_at' => now()->addMinutes(30),
            'duration_minutes' => 30,
            'status' => Exam::STATUS_RUNNING,
            'created_by' => $admin->id,
            'class_id' => $classB->id,
            'school_year_id' => $yearB->id,
        ]);

        $globalExam = Exam::create([
            'title' => 'Exam Global',
            'start_at' => now()->subMinutes(5),
            'end_at' => now()->addMinutes(30),
            'duration_minutes' => 30,
            'status' => Exam::STATUS_RUNNING,
            'created_by' => $admin->id,
            'class_id' => null,
            'school_year_id' => null,
        ]);

        $this->actingAs($peserta)
            ->post(route('student.exams.start', $foreignScopedExam))
            ->assertForbidden();

        $this->actingAs($peserta)
            ->post(route('student.exams.start', $globalExam))
            ->assertRedirect();
    }

    public function test_peserta_exam_list_only_shows_running_exam_with_matching_domain_or_global(): void
    {
        Carbon::setTestNow(now());

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $peserta = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $year = SchoolYear::create([
            'name' => '2026/2027',
            'is_active' => true,
        ]);
        $otherYear = SchoolYear::create([
            'name' => '2027/2028',
            'is_active' => false,
        ]);

        $class = SchoolClass::create([
            'name' => 'XI-IPA-1',
            'grade_level' => 11,
            'school_year_id' => $year->id,
        ]);
        $otherClass = SchoolClass::create([
            'name' => 'XI-IPA-2',
            'grade_level' => 11,
            'school_year_id' => $otherYear->id,
        ]);

        $peserta->update(['class_id' => $class->id]);

        $globalExam = Exam::create([
            'title' => 'Exam Global Visible',
            'start_at' => now()->subMinutes(2),
            'end_at' => now()->addMinutes(20),
            'duration_minutes' => 20,
            'status' => Exam::STATUS_RUNNING,
            'created_by' => $admin->id,
        ]);

        $matchingExam = Exam::create([
            'title' => 'Exam Domain Cocok',
            'start_at' => now()->subMinutes(2),
            'end_at' => now()->addMinutes(20),
            'duration_minutes' => 20,
            'status' => Exam::STATUS_RUNNING,
            'created_by' => $admin->id,
            'class_id' => $class->id,
            'school_year_id' => $year->id,
        ]);

        Exam::create([
            'title' => 'Exam Domain Beda',
            'start_at' => now()->subMinutes(2),
            'end_at' => now()->addMinutes(20),
            'duration_minutes' => 20,
            'status' => Exam::STATUS_RUNNING,
            'created_by' => $admin->id,
            'class_id' => $otherClass->id,
            'school_year_id' => $otherYear->id,
        ]);

        $response = $this->actingAs($peserta)
            ->get(route('student.exams.index'));

        $response->assertOk();
        $response->assertSee($globalExam->title);
        $response->assertSee($matchingExam->title);
        $response->assertDontSee('Exam Domain Beda');
    }
}


