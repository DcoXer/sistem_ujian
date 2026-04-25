<?php

namespace Tests\Feature\Student;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamOption;
use App\Models\ExamQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ExamShowPageTest extends TestCase
{
    use RefreshDatabase;

    private function makeActiveAttemptWithQuestions(User $student): ExamAttempt
    {
        Carbon::setTestNow(now());

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $exam = Exam::create([
            'title' => 'Ujian PKN',
            'start_at' => now()->subMinutes(5),
            'end_at' => now()->addHour(),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_RUNNING,
            'created_by' => $admin->id,
        ]);

        $question = ExamQuestion::create([
            'exam_id' => $exam->id,
            'question_text' => 'Apa nama presiden pertama Indonesia?',
            'points' => 10,
            'order' => 1,
        ]);

        foreach (['Soekarno', 'Soeharto', 'Habibie', 'Gus Dur'] as $index => $text) {
            ExamOption::create([
                'exam_question_id' => $question->id,
                'option_text' => $text,
                'is_correct' => $index === 0,
            ]);
        }

        return ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'status' => ExamAttempt::STATUS_ACTIVE,
            'started_at' => now()->subMinutes(3),
        ]);
    }

    public function test_exam_show_page_renders_with_exam_title(): void
    {
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $attempt = $this->makeActiveAttemptWithQuestions($student);

        $this->actingAs($student)
            ->get(route('student.exams.show', $attempt))
            ->assertOk()
            ->assertSee('Ujian PKN');
    }

    public function test_exam_show_page_displays_timer_element(): void
    {
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $attempt = $this->makeActiveAttemptWithQuestions($student);

        $this->actingAs($student)
            ->get(route('student.exams.show', $attempt))
            ->assertOk()
            ->assertSee('Sisa Waktu')
            ->assertSee('timer-display', false);
    }

    public function test_exam_show_page_displays_question_text(): void
    {
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $attempt = $this->makeActiveAttemptWithQuestions($student);

        $this->actingAs($student)
            ->get(route('student.exams.show', $attempt))
            ->assertOk()
            ->assertSee('Apa nama presiden pertama Indonesia?');
    }

    public function test_exam_show_page_renders_options_with_letter_labels(): void
    {
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $attempt = $this->makeActiveAttemptWithQuestions($student);

        $response = $this->actingAs($student)
            ->get(route('student.exams.show', $attempt))
            ->assertOk();

        // Pilihan ditampilkan dengan label huruf A, B, C, D
        $response->assertSee('Soekarno')
            ->assertSee('Soeharto')
            ->assertSee('Habibie')
            ->assertSee('Gus Dur');

        // Label huruf A–D ada di halaman
        $content = $response->getContent();
        $this->assertStringContainsString('>A<', $content);
        $this->assertStringContainsString('>B<', $content);
        $this->assertStringContainsString('>C<', $content);
        $this->assertStringContainsString('>D<', $content);
    }

    public function test_exam_show_page_displays_progress_bar_and_step_label(): void
    {
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $attempt = $this->makeActiveAttemptWithQuestions($student);

        $this->actingAs($student)
            ->get(route('student.exams.show', $attempt))
            ->assertOk()
            ->assertSee('question-step-label', false)
            ->assertSee('progress-bar', false)
            ->assertSee('dari')
            ->assertSee('soal');
    }

    public function test_exam_show_page_has_navigation_buttons_in_indonesian(): void
    {
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $attempt = $this->makeActiveAttemptWithQuestions($student);

        $this->actingAs($student)
            ->get(route('student.exams.show', $attempt))
            ->assertOk()
            ->assertSee('Sebelumnya')
            ->assertSee('Kumpulkan Jawaban');
    }

    public function test_exam_show_page_shows_autosave_indicator(): void
    {
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $attempt = $this->makeActiveAttemptWithQuestions($student);

        $this->actingAs($student)
            ->get(route('student.exams.show', $attempt))
            ->assertOk()
            ->assertSee('Autosimpan aktif');
    }

    public function test_exam_show_page_is_inaccessible_by_other_student(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $other = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $attempt = $this->makeActiveAttemptWithQuestions($owner);

        $this->actingAs($other)
            ->get(route('student.exams.show', $attempt))
            ->assertForbidden();
    }
}
