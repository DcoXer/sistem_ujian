<?php

namespace Tests\Feature\Student;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ExamResultPageTest extends TestCase
{
    use RefreshDatabase;

    private function makeFinishedAttempt(User $student, int $score): ExamAttempt
    {
        Carbon::setTestNow(now());

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $exam = Exam::create([
            'title' => 'Ujian Akhir Semester',
            'start_at' => now()->subHours(2),
            'end_at' => now()->subHour(),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_FINISHED,
            'created_by' => $admin->id,
        ]);

        return ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'status' => ExamAttempt::STATUS_FINISHED,
            'started_at' => now()->subHours(2),
            'submitted_at' => now()->subHour(),
            'answers_locked_at' => now()->subHour(),
            'scoring_processed_at' => now()->subHour(),
            'score' => $score,
        ]);
    }

    public function test_result_page_shows_exam_title_and_score(): void
    {
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $attempt = $this->makeFinishedAttempt($student, 80);

        $this->actingAs($student)
            ->get(route('student.exams.result', $attempt))
            ->assertOk()
            ->assertSee('Ujian Akhir Semester')
            ->assertSee('80');
    }

    public function test_result_page_shows_lulus_message_when_score_is_70_or_above(): void
    {
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $attempt = $this->makeFinishedAttempt($student, 70);

        $this->actingAs($student)
            ->get(route('student.exams.result', $attempt))
            ->assertOk()
            ->assertSee('Selamat, kamu lulus!');
    }

    public function test_result_page_shows_semangat_message_when_score_below_70(): void
    {
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $attempt = $this->makeFinishedAttempt($student, 60);

        $this->actingAs($student)
            ->get(route('student.exams.result', $attempt))
            ->assertOk()
            ->assertSee('Tetap semangat belajar!');
    }

    public function test_result_page_shows_sudah_dikumpulkan_status(): void
    {
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $attempt = $this->makeFinishedAttempt($student, 75);

        $this->actingAs($student)
            ->get(route('student.exams.result', $attempt))
            ->assertOk()
            ->assertSee('Sudah Dikumpulkan');
    }

    public function test_result_page_shows_navigation_links(): void
    {
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $attempt = $this->makeFinishedAttempt($student, 90);

        $this->actingAs($student)
            ->get(route('student.exams.result', $attempt))
            ->assertOk()
            ->assertSee('Kembali ke Daftar Ujian')
            ->assertSee('Ke Beranda');
    }

    public function test_result_page_is_inaccessible_by_other_student(): void
    {
        $owner = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $other = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $attempt = $this->makeFinishedAttempt($owner, 80);

        $this->actingAs($other)
            ->get(route('student.exams.result', $attempt))
            ->assertForbidden();
    }

    public function test_result_page_redirects_when_exam_not_finished_yet(): void
    {
        Carbon::setTestNow(now());

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $exam = Exam::create([
            'title' => 'Ujian Sedang Berjalan',
            'start_at' => now()->subMinutes(30),
            'end_at' => now()->addMinutes(30),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_RUNNING,
            'created_by' => $admin->id,
        ]);

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'status' => ExamAttempt::STATUS_SUBMITTED,
            'started_at' => now()->subMinutes(25),
            'submitted_at' => now()->subMinutes(5),
        ]);

        $this->actingAs($student)
            ->get(route('student.exams.result', $attempt))
            ->assertRedirect(route('student.exams.index'))
            ->assertSessionHas('error', 'Hasil belum tersedia. Tunggu sampai ujian selesai.');
    }
}
