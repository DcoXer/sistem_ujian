<?php

namespace Tests\Feature\Teacher;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\HomeroomTeacher;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeroomResultsTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_without_homeroom_assignment_cannot_access_homeroom_results_module(): void
    {
        $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);

        $this->actingAs($teacher)
            ->get(route('teacher.homeroom.results.index'))
            ->assertForbidden();
    }

    public function test_homeroom_teacher_can_view_results_for_assigned_class(): void
    {
        $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
        $class = SchoolClass::query()->create(['name' => '7A']);
        HomeroomTeacher::query()->create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
        ]);

        $student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
            'class_id' => $class->id,
            'nis' => '7001',
            'name' => 'Siswa Kelas 7A',
        ]);
        $subject = Subject::query()->create([
            'code' => 'MTK',
            'name' => 'Matematika',
            'is_active' => true,
        ]);
        $exam = Exam::query()->create([
            'title' => 'Ujian Matematika 7A',
            'start_at' => now()->subDays(2),
            'end_at' => now()->subDay(),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_FINISHED,
            'created_by' => $teacher->id,
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'class_id' => $class->id,
        ]);
        ExamAttempt::query()->create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'status' => ExamAttempt::STATUS_FINISHED,
            'started_at' => now()->subDays(2),
            'submitted_at' => now()->subDay(),
            'score' => 88,
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.homeroom.results.index'))
            ->assertOk()
            ->assertSee('Hasil Ujian Wali Kelas')
            ->assertSee('7A')
            ->assertSee('Siswa Kelas 7A')
            ->assertSee('Matematika')
            ->assertSee('88');
    }

    public function test_homeroom_export_excel_is_limited_to_assigned_classes(): void
    {
        $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
        $classA = SchoolClass::query()->create(['name' => '8A']);
        $classB = SchoolClass::query()->create(['name' => '8B']);
        HomeroomTeacher::query()->create([
            'teacher_id' => $teacher->id,
            'class_id' => $classA->id,
        ]);

        $subject = Subject::query()->create([
            'code' => 'IPA',
            'name' => 'Ilmu Pengetahuan Alam',
            'is_active' => true,
        ]);

        $studentA = User::factory()->create([
            'role' => User::ROLE_STUDENT,
            'class_id' => $classA->id,
            'nis' => '8001',
            'name' => 'Siswa 8A',
        ]);
        $studentB = User::factory()->create([
            'role' => User::ROLE_STUDENT,
            'class_id' => $classB->id,
            'nis' => '8002',
            'name' => 'Siswa 8B',
        ]);

        $examA = Exam::query()->create([
            'title' => 'Ujian IPA 8A',
            'start_at' => now()->subDays(2),
            'end_at' => now()->subDay(),
            'duration_minutes' => 45,
            'status' => Exam::STATUS_FINISHED,
            'created_by' => $teacher->id,
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'class_id' => $classA->id,
        ]);
        ExamAttempt::query()->create([
            'exam_id' => $examA->id,
            'user_id' => $studentA->id,
            'status' => ExamAttempt::STATUS_FINISHED,
            'started_at' => now()->subDays(2),
            'submitted_at' => now()->subDay(),
            'score' => 77,
        ]);

        $examB = Exam::query()->create([
            'title' => 'Ujian IPA 8B',
            'start_at' => now()->subDays(2),
            'end_at' => now()->subDay(),
            'duration_minutes' => 45,
            'status' => Exam::STATUS_FINISHED,
            'created_by' => $teacher->id,
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'class_id' => $classB->id,
        ]);
        ExamAttempt::query()->create([
            'exam_id' => $examB->id,
            'user_id' => $studentB->id,
            'status' => ExamAttempt::STATUS_FINISHED,
            'started_at' => now()->subDays(2),
            'submitted_at' => now()->subDay(),
            'score' => 66,
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.homeroom.results.index'))
            ->assertOk()
            ->assertSee('Siswa 8A')
            ->assertDontSee('Siswa 8B');

        $response = $this->actingAs($teacher)
            ->get(route('teacher.homeroom.results.export', ['format' => 'excel']));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_homeroom_export_pdf_is_available_for_assigned_class(): void
    {
        $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
        $class = SchoolClass::query()->create(['name' => '9A']);
        HomeroomTeacher::query()->create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
        ]);

        $subject = Subject::query()->create([
            'code' => 'BIN',
            'name' => 'Bahasa Indonesia',
            'is_active' => true,
        ]);
        $student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
            'class_id' => $class->id,
            'nis' => '9001',
            'name' => 'Siswa 9A',
        ]);
        $exam = Exam::query()->create([
            'title' => 'Ujian Bahasa 9A',
            'start_at' => now()->subDays(2),
            'end_at' => now()->subDay(),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_FINISHED,
            'created_by' => $teacher->id,
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'class_id' => $class->id,
        ]);
        ExamAttempt::query()->create([
            'exam_id' => $exam->id,
            'user_id' => $student->id,
            'status' => ExamAttempt::STATUS_FINISHED,
            'started_at' => now()->subDays(2),
            'submitted_at' => now()->subDay(),
            'score' => 91,
        ]);

        $pdfResponse = $this->actingAs($teacher)
            ->get(route('teacher.homeroom.results.export', ['format' => 'pdf']));
        $pdfResponse->assertOk();
        $pdfResponse->assertHeader('content-type', 'application/pdf');
    }
}
