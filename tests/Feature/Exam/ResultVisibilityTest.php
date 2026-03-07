<?php

namespace Tests\Feature\Exam;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ResultVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_peserta_cannot_access_result_before_exam_finished(): void
    {
        Carbon::setTestNow(now());

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $peserta = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $exam = Exam::create([
            'title' => 'Result Visibility Exam',
            'start_at' => now()->subMinutes(10),
            'end_at' => now()->addMinutes(20),
            'duration_minutes' => 30,
            'status' => Exam::STATUS_RUNNING,
            'created_by' => $admin->id,
        ]);

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $peserta->id,
            'status' => ExamAttempt::STATUS_SUBMITTED,
            'started_at' => now()->subMinutes(8),
            'submitted_at' => now()->subMinutes(2),
        ]);

        $this->actingAs($peserta)
            ->get(route('student.exams.result', $attempt))
            ->assertRedirect(route('student.exams.index'))
            ->assertSessionHas('error', 'Hasil belum tersedia. Tunggu sampai ujian selesai.');
    }

    public function test_peserta_can_access_result_after_exam_finished(): void
    {
        Carbon::setTestNow(now());

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $peserta = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $exam = Exam::create([
            'title' => 'Result Final Exam',
            'start_at' => now()->subHour(),
            'end_at' => now()->subMinutes(10),
            'duration_minutes' => 30,
            'status' => Exam::STATUS_FINISHED,
            'created_by' => $admin->id,
        ]);

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $peserta->id,
            'status' => ExamAttempt::STATUS_FINISHED,
            'started_at' => now()->subHours(2),
            'submitted_at' => now()->subHour(),
            'answers_locked_at' => now()->subHour(),
            'scoring_processed_at' => now()->subHour(),
            'score' => 80,
        ]);

        $this->actingAs($peserta)
            ->get(route('student.exams.result', $attempt))
            ->assertOk();
    }

    public function test_peserta_dashboard_hides_score_before_exam_finished(): void
    {
        Carbon::setTestNow(now());

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $peserta = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $exam = Exam::create([
            'title' => 'Dashboard Hidden Score Exam',
            'start_at' => now()->subMinutes(10),
            'end_at' => now()->addMinutes(20),
            'duration_minutes' => 30,
            'status' => Exam::STATUS_RUNNING,
            'created_by' => $admin->id,
        ]);

        ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $peserta->id,
            'status' => ExamAttempt::STATUS_FINISHED,
            'started_at' => now()->subMinutes(9),
            'submitted_at' => now()->subMinutes(3),
            'answers_locked_at' => now()->subMinutes(3),
            'scoring_processed_at' => now()->subMinutes(3),
            'score' => 90,
        ]);

        $this->actingAs($peserta)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('90 pts')
            ->assertDontSee('Hasil Terakhir');
    }
}


