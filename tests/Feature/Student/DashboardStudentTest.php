<?php

namespace Tests\Feature\Student;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardStudentTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_dashboard_shows_active_exam_cta_when_exam_is_running(): void
    {
        Carbon::setTestNow(now());

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

        Exam::create([
            'title' => 'Ujian Matematika Aktif',
            'start_at' => now()->subMinutes(5),
            'end_at' => now()->addHour(),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_RUNNING,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($student)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Ujian Sedang Berlangsung')
            ->assertSee('Ujian Matematika Aktif')
            ->assertSee('Kerjakan Sekarang');
    }

    public function test_student_dashboard_shows_lanjut_when_has_in_progress_attempt_from_out_of_scope_exam(): void
    {
        Carbon::setTestNow(now());

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        // Student tanpa kelas — scope domain akan memfilter exam yang punya class_id
        $student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
            'class_id' => null,
        ]);

        // Exam ini punya class_id, sehingga tidak muncul di $activeExam untuk student tanpa kelas
        $schoolClass = \App\Models\SchoolClass::query()->create(['name' => '4A', 'grade_level' => 4]);
        $exam = Exam::create([
            'title' => 'Ujian IPA Kelas 4',
            'start_at' => now()->subMinutes(10),
            'end_at' => now()->addHour(),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_RUNNING,
            'created_by' => $admin->id,
            'class_id' => $schoolClass->id,
        ]);

        // Buat attempt aktif langsung (bypass policy) — masih dalam window waktu ujian
        ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'status' => ExamAttempt::STATUS_ACTIVE,
            'started_at' => now()->subMinutes(5),
        ]);

        // $activeExam = null karena exam punya class_id dan student tanpa kelas
        // $myInProgressAttempt = attempt aktif di atas → tampil "Lanjut Mengerjakan"
        $this->actingAs($student)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Lanjut Mengerjakan');
    }

    public function test_student_dashboard_shows_no_active_exam_message_when_idle(): void
    {
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $this->actingAs($student)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Tidak ada ujian aktif');
    }

    public function test_student_dashboard_shows_nilai_terakhirku_section(): void
    {
        Carbon::setTestNow(now());

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $exam = Exam::create([
            'title' => 'Ujian Bahasa Indonesia',
            'start_at' => now()->subHours(2),
            'end_at' => now()->subHour(),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_FINISHED,
            'created_by' => $admin->id,
        ]);

        ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'status' => ExamAttempt::STATUS_FINISHED,
            'started_at' => now()->subHours(2),
            'submitted_at' => now()->subHours(1),
            'answers_locked_at' => now()->subHours(1),
            'scoring_processed_at' => now()->subHours(1),
            'score' => 85,
        ]);

        $this->actingAs($student)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Nilai Terakhirku')
            ->assertSee('Ujian Bahasa Indonesia')
            ->assertSee('85');
    }

    public function test_student_dashboard_shows_empty_state_when_no_scores(): void
    {
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $this->actingAs($student)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Nilai Terakhirku')
            ->assertSee('Belum ada nilai');
    }

    public function test_student_dashboard_shows_lihat_semua_ujian_link_when_no_active_exam(): void
    {
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $this->actingAs($student)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Lihat Semua Ujian');
    }
}
