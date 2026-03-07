<?php

namespace Tests\Feature\Exam;

use App\Models\Exam;
use App\Models\ExamOption;
use App\Models\ExamQuestion;
use App\Models\User;
use App\Services\ExamLifecycleService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_exam_lifecycle_publish_running_finished(): void
    {
        Carbon::setTestNow(now());

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $author = User::factory()->create(['role' => User::ROLE_TEACHER]);
        $exam = Exam::create([
            'title' => 'Lifecycle Exam',
            'start_at' => now()->subMinute(),
            'end_at' => now()->addMinute(),
            'authoring_start_at' => now()->subHours(2),
            'authoring_end_at' => now()->subHour(),
            'duration_minutes' => 30,
            'status' => Exam::STATUS_DRAFT,
            'created_by' => $admin->id,
            'author_id' => $author->id,
        ]);

        $question = ExamQuestion::create([
            'exam_id' => $exam->id,
            'question_text' => '1 + 1 = ?',
            'points' => 10,
            'order' => 1,
        ]);

        ExamOption::create([
            'exam_question_id' => $question->id,
            'option_text' => '2',
            'is_correct' => true,
        ]);
        ExamOption::create([
            'exam_question_id' => $question->id,
            'option_text' => '3',
            'is_correct' => false,
        ]);

        /** @var ExamLifecycleService $lifecycle */
        $lifecycle = app(ExamLifecycleService::class);
        $published = $lifecycle->publishDraftExam($exam);

        $this->assertSame(Exam::STATUS_RUNNING, $published->status);

        Carbon::setTestNow(now()->addMinutes(3));
        $lifecycle->closeExpiredExams();

        $this->assertSame(Exam::STATUS_FINISHED, $exam->fresh()->status);
    }

    public function test_finished_exam_cannot_be_published_again(): void
    {
        Carbon::setTestNow(now());

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $exam = Exam::create([
            'title' => 'Locked Finished Exam',
            'start_at' => now()->subHours(2),
            'end_at' => now()->subHour(),
            'authoring_start_at' => now()->subDays(3),
            'authoring_end_at' => now()->subDays(2),
            'duration_minutes' => 30,
            'status' => Exam::STATUS_FINISHED,
            'created_by' => $admin->id,
        ]);

        $question = ExamQuestion::create([
            'exam_id' => $exam->id,
            'question_text' => '2 + 2 = ?',
            'points' => 10,
            'order' => 1,
        ]);

        ExamOption::create([
            'exam_question_id' => $question->id,
            'option_text' => '4',
            'is_correct' => true,
        ]);
        ExamOption::create([
            'exam_question_id' => $question->id,
            'option_text' => '5',
            'is_correct' => false,
        ]);

        $this->expectException(AuthorizationException::class);

        /** @var ExamLifecycleService $lifecycle */
        $lifecycle = app(ExamLifecycleService::class);
        $lifecycle->publishDraftExam($exam);
    }

    public function test_exam_cannot_be_published_before_authoring_window_ends(): void
    {
        Carbon::setTestNow(now());

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $author = User::factory()->create(['role' => User::ROLE_TEACHER]);
        $exam = Exam::create([
            'title' => 'Authoring Window Open Exam',
            'start_at' => now()->addHours(2),
            'end_at' => now()->addHours(4),
            'authoring_start_at' => now()->subHour(),
            'authoring_end_at' => now()->addHour(),
            'duration_minutes' => 30,
            'status' => Exam::STATUS_DRAFT,
            'created_by' => $admin->id,
            'author_id' => $author->id,
        ]);

        $question = ExamQuestion::create([
            'exam_id' => $exam->id,
            'question_text' => '1 + 1 = ?',
            'points' => 10,
            'order' => 1,
        ]);

        ExamOption::create([
            'exam_question_id' => $question->id,
            'option_text' => '2',
            'is_correct' => true,
        ]);
        ExamOption::create([
            'exam_question_id' => $question->id,
            'option_text' => '3',
            'is_correct' => false,
        ]);

        $this->expectException(AuthorizationException::class);

        /** @var ExamLifecycleService $lifecycle */
        $lifecycle = app(ExamLifecycleService::class);
        $lifecycle->publishDraftExam($exam);
    }
}

