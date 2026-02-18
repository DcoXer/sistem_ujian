<?php

namespace Tests\Feature\Exam;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AuthorQuestionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_can_add_and_update_question_on_draft_exam(): void
    {
        Carbon::setTestNow(now());

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $author = User::factory()->create(['role' => User::ROLE_AUTHOR]);

        $exam = Exam::create([
            'title' => 'Author Draft Exam',
            'start_at' => now()->addHour(),
            'end_at' => now()->addHours(2),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_DRAFT,
            'created_by' => $admin->id,
            'author_id' => $author->id,
        ]);

        $this->actingAs($author)
            ->post(route('author.exams.questions.store', $exam), [
                'question_text' => '1 + 1 = ?',
                'points' => 10,
                'order' => 1,
                'options' => ['1', '2', '3', '4'],
                'correct_option' => 1,
            ])
            ->assertSessionHasNoErrors();

        $question = ExamQuestion::query()->where('exam_id', $exam->id)->first();
        $this->assertNotNull($question);

        $this->actingAs($author)
            ->put(route('author.exams.questions.update', [$exam, $question]), [
                'question_text' => '2 + 2 = ?',
                'points' => 20,
                'order' => 1,
                'options' => ['2', '3', '4', '5'],
                'correct_option' => 2,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('exam_questions', [
            'id' => $question->id,
            'question_text' => '2 + 2 = ?',
            'points' => 20,
        ]);
    }

    public function test_admin_cannot_access_author_question_routes(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $exam = Exam::create([
            'title' => 'No Admin Authoring',
            'start_at' => now()->addHour(),
            'end_at' => now()->addHours(2),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_DRAFT,
            'created_by' => $admin->id,
            'author_id' => User::factory()->create(['role' => User::ROLE_AUTHOR])->id,
        ]);

        $this->actingAs($admin)
            ->post(route('author.exams.questions.store', $exam), [
                'question_text' => 'X',
                'points' => 10,
                'order' => 1,
                'options' => ['A', 'B'],
                'correct_option' => 0,
            ])
            ->assertForbidden();
    }

    public function test_author_cannot_manage_questions_after_exam_is_not_draft(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $author = User::factory()->create(['role' => User::ROLE_AUTHOR]);
        $exam = Exam::create([
            'title' => 'Running Exam',
            'start_at' => now()->subHour(),
            'end_at' => now()->addHour(),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_RUNNING,
            'created_by' => $admin->id,
            'author_id' => $author->id,
        ]);

        $this->actingAs($author)
            ->post(route('author.exams.questions.store', $exam), [
                'question_text' => 'Tidak boleh',
                'points' => 10,
                'order' => 1,
                'options' => ['A', 'B'],
                'correct_option' => 0,
            ])
            ->assertForbidden();
    }

    public function test_author_cannot_manage_exam_assigned_to_other_author(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $authorA = User::factory()->create(['role' => User::ROLE_AUTHOR]);
        $authorB = User::factory()->create(['role' => User::ROLE_AUTHOR]);

        $exam = Exam::create([
            'title' => 'Assigned To Other Author',
            'start_at' => now()->addHour(),
            'end_at' => now()->addHours(2),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_DRAFT,
            'created_by' => $admin->id,
            'author_id' => $authorA->id,
        ]);

        $this->actingAs($authorB)
            ->post(route('author.exams.questions.store', $exam), [
                'question_text' => 'Tidak boleh',
                'points' => 10,
                'order' => 1,
                'options' => ['A', 'B'],
                'correct_option' => 0,
            ])
            ->assertForbidden();
    }

    public function test_author_cannot_manage_questions_when_schedule_is_over_even_if_still_draft(): void
    {
        Carbon::setTestNow(now());

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $author = User::factory()->create(['role' => User::ROLE_AUTHOR]);

        $exam = Exam::create([
            'title' => 'Expired Draft Exam',
            'start_at' => now()->subDays(2),
            'end_at' => now()->subDay(),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_DRAFT,
            'created_by' => $admin->id,
            'author_id' => $author->id,
        ]);

        $this->actingAs($author)
            ->post(route('author.exams.questions.store', $exam), [
                'question_text' => 'Tidak boleh lewat jadwal',
                'points' => 10,
                'order' => 1,
                'options' => ['A', 'B'],
                'correct_option' => 0,
            ])
            ->assertForbidden();
    }

    public function test_author_can_view_questions_page_after_exam_finished(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $author = User::factory()->create(['role' => User::ROLE_AUTHOR]);

        $exam = Exam::create([
            'title' => 'Finished Exam View',
            'start_at' => now()->subHours(2),
            'end_at' => now()->subHour(),
            'authoring_start_at' => now()->subDays(3),
            'authoring_end_at' => now()->subDays(2),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_FINISHED,
            'created_by' => $admin->id,
            'author_id' => $author->id,
        ]);

        $this->actingAs($author)
            ->get(route('author.exams.show', $exam))
            ->assertOk()
            ->assertSee('Soal ditampilkan read-only');
    }

    public function test_author_cannot_view_questions_page_while_exam_running(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $author = User::factory()->create(['role' => User::ROLE_AUTHOR]);

        $exam = Exam::create([
            'title' => 'Running Exam No Author View',
            'start_at' => now()->subMinutes(30),
            'end_at' => now()->addMinutes(30),
            'authoring_start_at' => now()->subDays(2),
            'authoring_end_at' => now()->subDay(),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_RUNNING,
            'created_by' => $admin->id,
            'author_id' => $author->id,
        ]);

        $this->actingAs($author)
            ->get(route('author.exams.show', $exam))
            ->assertForbidden();
    }

    public function test_author_cannot_create_question_with_non_sequential_order(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $author = User::factory()->create(['role' => User::ROLE_AUTHOR]);

        $exam = Exam::create([
            'title' => 'Sequential Order Guard',
            'start_at' => now()->addHour(),
            'end_at' => now()->addHours(2),
            'authoring_start_at' => now()->subHour(),
            'authoring_end_at' => now()->addMinutes(30),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_DRAFT,
            'created_by' => $admin->id,
            'author_id' => $author->id,
        ]);

        ExamQuestion::create([
            'exam_id' => $exam->id,
            'question_text' => 'Soal awal',
            'points' => 10,
            'order' => 1,
        ]);

        $this->actingAs($author)
            ->from(route('author.exams.show', $exam))
            ->post(route('author.exams.questions.store', $exam), [
                'question_text' => 'Soal loncat',
                'points' => 10,
                'order' => 50,
                'options' => ['A', 'B'],
                'correct_option' => 0,
            ])
            ->assertSessionHasErrors('order');
    }

    public function test_author_cannot_submit_html_tags_in_question_payload(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $author = User::factory()->create(['role' => User::ROLE_AUTHOR]);

        $exam = Exam::create([
            'title' => 'HTML Guard',
            'start_at' => now()->addHour(),
            'end_at' => now()->addHours(2),
            'authoring_start_at' => now()->subHour(),
            'authoring_end_at' => now()->addMinutes(30),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_DRAFT,
            'created_by' => $admin->id,
            'author_id' => $author->id,
        ]);

        $this->actingAs($author)
            ->from(route('author.exams.show', $exam))
            ->post(route('author.exams.questions.store', $exam), [
                'question_text' => '<script>alert(1)</script>',
                'points' => 10,
                'order' => 1,
                'options' => ['A', 'B'],
                'correct_option' => 0,
            ])
            ->assertSessionHasErrors('question_text');
    }

    public function test_author_cannot_exceed_max_question_limit_per_exam(): void
    {
        config()->set('exam.author_max_questions_per_exam', 1);

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $author = User::factory()->create(['role' => User::ROLE_AUTHOR]);

        $exam = Exam::create([
            'title' => 'Limit Guard Exam',
            'start_at' => now()->addHour(),
            'end_at' => now()->addHours(2),
            'authoring_start_at' => now()->subHour(),
            'authoring_end_at' => now()->addMinutes(30),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_DRAFT,
            'created_by' => $admin->id,
            'author_id' => $author->id,
        ]);

        ExamQuestion::create([
            'exam_id' => $exam->id,
            'question_text' => 'Soal ke-1',
            'points' => 10,
            'order' => 1,
        ]);

        $this->actingAs($author)
            ->from(route('author.exams.show', $exam))
            ->post(route('author.exams.questions.store', $exam), [
                'question_text' => 'Soal ke-2',
                'points' => 10,
                'order' => 2,
                'options' => ['A', 'B'],
                'correct_option' => 0,
            ])
            ->assertSessionHasErrors('question_limit');
    }

    public function test_author_cannot_change_question_order_after_created(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $author = User::factory()->create(['role' => User::ROLE_AUTHOR]);

        $exam = Exam::create([
            'title' => 'Immutable Question Order',
            'start_at' => now()->addHour(),
            'end_at' => now()->addHours(2),
            'authoring_start_at' => now()->subHour(),
            'authoring_end_at' => now()->addMinutes(30),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_DRAFT,
            'created_by' => $admin->id,
            'author_id' => $author->id,
        ]);

        $question = ExamQuestion::create([
            'exam_id' => $exam->id,
            'question_text' => 'Soal awal',
            'points' => 10,
            'order' => 1,
        ]);

        $question->options()->createMany([
            ['option_text' => 'A', 'is_correct' => true],
            ['option_text' => 'B', 'is_correct' => false],
        ]);

        $this->actingAs($author)
            ->from(route('author.exams.show', $exam))
            ->put(route('author.exams.questions.update', [$exam, $question]), [
                'question_text' => 'Soal update',
                'points' => 20,
                'order' => 2,
                'options' => ['A', 'B'],
                'correct_option' => 0,
            ])
            ->assertSessionHasErrors('order');

        $this->assertSame(1, (int) $question->fresh()->order);
    }

    public function test_author_can_delete_question_and_remaining_orders_are_reindexed(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $author = User::factory()->create(['role' => User::ROLE_AUTHOR]);

        $exam = Exam::create([
            'title' => 'Delete Question Exam',
            'start_at' => now()->addHour(),
            'end_at' => now()->addHours(2),
            'authoring_start_at' => now()->subHour(),
            'authoring_end_at' => now()->addMinutes(30),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_DRAFT,
            'created_by' => $admin->id,
            'author_id' => $author->id,
        ]);

        $q1 = ExamQuestion::create([
            'exam_id' => $exam->id,
            'question_text' => 'Soal 1',
            'points' => 10,
            'order' => 1,
        ]);

        $q2 = ExamQuestion::create([
            'exam_id' => $exam->id,
            'question_text' => 'Soal 2',
            'points' => 10,
            'order' => 2,
        ]);

        $this->actingAs($author)
            ->delete(route('author.exams.questions.destroy', [$exam, $q1]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('exam_questions', ['id' => $q1->id]);
        $this->assertDatabaseHas('exam_questions', ['id' => $q2->id, 'order' => 1]);
    }

    public function test_author_cannot_delete_question_when_exam_has_attempts(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $author = User::factory()->create(['role' => User::ROLE_AUTHOR]);
        $peserta = User::factory()->create(['role' => User::ROLE_PESERTA]);

        $exam = Exam::create([
            'title' => 'No Delete With Attempts',
            'start_at' => now()->addHour(),
            'end_at' => now()->addHours(2),
            'authoring_start_at' => now()->subHour(),
            'authoring_end_at' => now()->addMinutes(30),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_DRAFT,
            'created_by' => $admin->id,
            'author_id' => $author->id,
        ]);

        $question = ExamQuestion::create([
            'exam_id' => $exam->id,
            'question_text' => 'Soal tetap ada',
            'points' => 10,
            'order' => 1,
        ]);

        ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $peserta->id,
            'status' => 'active',
            'started_at' => now(),
        ]);

        $this->actingAs($author)
            ->delete(route('author.exams.questions.destroy', [$exam, $question]))
            ->assertSessionHasErrors('question_delete');

        $this->assertDatabaseHas('exam_questions', ['id' => $question->id]);
    }
}
