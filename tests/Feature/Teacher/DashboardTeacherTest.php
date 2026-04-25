<?php

namespace Tests\Feature\Teacher;

use App\Models\Exam;
use App\Models\HomeroomTeacher;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardTeacherTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_dashboard_shows_welcome_with_name(): void
    {
        $teacher = User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'name' => 'Budi Santoso',
        ]);

        $this->actingAs($teacher)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee('Panel Guru');
    }

    public function test_teacher_dashboard_shows_kelola_soal_link(): void
    {
        $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);

        $this->actingAs($teacher)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Kelola Soal')
            ->assertSee(route('teacher.exams.index'));
    }

    public function test_teacher_dashboard_shows_authoring_guidance_info(): void
    {
        $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);

        $this->actingAs($teacher)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Cara kerja pembuatan soal');
    }

    public function test_teacher_with_homeroom_sees_nilai_wali_kelas_link(): void
    {
        $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
        $class = SchoolClass::query()->create(['name' => '3A', 'grade_level' => 3]);
        HomeroomTeacher::query()->create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
        ]);

        $this->actingAs($teacher)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Nilai Wali Kelas');
    }

    public function test_teacher_without_homeroom_does_not_see_nilai_wali_kelas_link(): void
    {
        $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);

        $this->actingAs($teacher)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Nilai Wali Kelas');
    }

    public function test_teacher_dashboard_shows_empty_state_when_no_exams(): void
    {
        $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);

        $this->actingAs($teacher)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Tidak ada ujian yang perlu dikerjakan saat ini');
    }

    public function test_teacher_dashboard_shows_latest_exams_list(): void
    {
        Carbon::setTestNow(now());

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);

        Exam::create([
            'title' => 'Ujian IPS Kelas 3',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDays(2),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_DRAFT,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($teacher)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Ujian IPS Kelas 3')
            ->assertSee('Persiapan');
    }
}
