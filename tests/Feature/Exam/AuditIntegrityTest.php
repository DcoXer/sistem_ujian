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

class AuditIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_publish_exam_writes_security_audit_with_ip_and_user_agent(): void
    {
        Carbon::setTestNow(now());

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $author = User::factory()->create(['role' => User::ROLE_TEACHER]);

        $exam = Exam::create([
            'title' => 'Audit Publish Exam',
            'start_at' => now()->addHour(),
            'end_at' => now()->addHours(2),
            'authoring_start_at' => now()->subDays(2),
            'authoring_end_at' => now()->subHour(),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_DRAFT,
            'created_by' => $admin->id,
            'author_id' => $author->id,
        ]);

        $question = ExamQuestion::create([
            'exam_id' => $exam->id,
            'question_text' => '1 + 1?',
            'points' => 10,
            'order' => 1,
        ]);

        $question->options()->createMany([
            ['option_text' => '1', 'is_correct' => false],
            ['option_text' => '2', 'is_correct' => true],
        ]);

        $this->actingAs($admin)
            ->withServerVariables(['REMOTE_ADDR' => '10.10.10.10'])
            ->withHeader('User-Agent', 'AuditTester/1.0')
            ->post(route('admin.exams.publish', $exam))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('security_audits', [
            'action' => 'exam_published',
            'actor_user_id' => $admin->id,
            'target_type' => 'Exam',
            'target_id' => $exam->id,
            'ip_address' => '10.10.10.10',
            'user_agent' => 'AuditTester/1.0',
        ]);
    }

    public function test_submit_attempt_writes_security_audit_with_ip_and_user_agent(): void
    {
        Carbon::setTestNow(now());

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $peserta = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $exam = Exam::create([
            'title' => 'Audit Submit Exam',
            'start_at' => now()->subHour(),
            'end_at' => now()->addHour(),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_RUNNING,
            'created_by' => $admin->id,
        ]);

        $question = ExamQuestion::create([
            'exam_id' => $exam->id,
            'question_text' => '2 + 2?',
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
            'status' => ExamAttempt::STATUS_ACTIVE,
            'started_at' => now()->subMinutes(10),
        ]);

        // Ensure at least one answer exists before submit.
        $this->actingAs($peserta)
            ->post(route('student.exams.answer', $attempt), [
                'question_id' => $question->id,
                'option_id' => $option->id,
            ]);

        $this->actingAs($peserta)
            ->withServerVariables(['REMOTE_ADDR' => '10.10.10.11'])
            ->withHeader('User-Agent', 'AuditTester/2.0')
            ->post(route('student.exams.submit', $attempt))
            ->assertRedirect();

        $this->assertDatabaseHas('security_audits', [
            'action' => 'attempt_submitted',
            'actor_user_id' => $peserta->id,
            'target_type' => 'ExamAttempt',
            'target_id' => $attempt->id,
            'ip_address' => '10.10.10.11',
            'user_agent' => 'AuditTester/2.0',
        ]);
    }
}



