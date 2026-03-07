<?php

namespace Tests\Feature\Security;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamOption;
use App\Models\ExamQuestion;
use App\Models\HomeroomTeacher;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionMatrixTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_matrix_for_core_get_routes(): void
    {
        [$admin, $operator, $teacherAssigned, $teacherUnassigned, $student] = $this->seedRoleContext();

        $matrix = [
            'admin.exams.index' => [
                User::ROLE_ADMIN => 200,
                User::ROLE_OPERATOR => 403,
                User::ROLE_TEACHER => 403,
                User::ROLE_STUDENT => 403,
            ],
            'admin.subjects.index' => [
                User::ROLE_ADMIN => 200,
                User::ROLE_OPERATOR => 403,
                User::ROLE_TEACHER => 403,
                User::ROLE_STUDENT => 403,
            ],
            'operator.exams.index' => [
                User::ROLE_ADMIN => 403,
                User::ROLE_OPERATOR => 200,
                User::ROLE_TEACHER => 403,
                User::ROLE_STUDENT => 403,
            ],
            'teacher.exams.index' => [
                User::ROLE_ADMIN => 403,
                User::ROLE_OPERATOR => 403,
                User::ROLE_TEACHER => 200,
                User::ROLE_STUDENT => 403,
            ],
            'teacher.homeroom.results.index' => [
                User::ROLE_ADMIN => 403,
                User::ROLE_OPERATOR => 403,
                'teacher_assigned' => 200,
                'teacher_unassigned' => 403,
                User::ROLE_STUDENT => 403,
            ],
            'student.exams.index' => [
                User::ROLE_ADMIN => 403,
                User::ROLE_OPERATOR => 403,
                User::ROLE_TEACHER => 403,
                User::ROLE_STUDENT => 200,
            ],
        ];

        $users = [
            User::ROLE_ADMIN => $admin,
            User::ROLE_OPERATOR => $operator,
            User::ROLE_TEACHER => $teacherAssigned,
            User::ROLE_STUDENT => $student,
            'teacher_assigned' => $teacherAssigned,
            'teacher_unassigned' => $teacherUnassigned,
        ];

        foreach ($matrix as $routeName => $expectations) {
            $this->app['auth']->forgetGuards();
            $guestResponse = $this->get(route($routeName));
            $this->assertTrue(
                in_array($guestResponse->getStatusCode(), [302, 403], true),
                "Guest access to {$routeName} should be blocked."
            );

            foreach ($expectations as $key => $expectedStatus) {
                $user = $users[$key];
                $this->actingAs($user)
                    ->get(route($routeName))
                    ->assertStatus($expectedStatus);
            }
        }
    }

    public function test_role_matrix_for_sensitive_post_routes(): void
    {
        [$admin, $operator, $teacherAssigned, $teacherUnassigned, $student] = $this->seedRoleContext();

        $teacherForExam = User::factory()->create(['role' => User::ROLE_TEACHER]);

        $publishableExam = Exam::create([
            'title' => 'Publish Matrix Exam',
            'start_at' => now()->addHours(2),
            'end_at' => now()->addHours(3),
            'authoring_start_at' => now()->subDays(2),
            'authoring_end_at' => now()->subHour(),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_DRAFT,
            'created_by' => $admin->id,
            'teacher_id' => $teacherForExam->id,
            'author_id' => $teacherForExam->id,
        ]);

        $question = ExamQuestion::create([
            'exam_id' => $publishableExam->id,
            'question_text' => '2+2?',
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

        $operatorExam = Exam::create([
            'title' => 'Operator Matrix Exam',
            'start_at' => now()->subHour(),
            'end_at' => now()->addHour(),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_RUNNING,
            'created_by' => $admin->id,
        ]);
        $operatorAttempt = ExamAttempt::create([
            'exam_id' => $operatorExam->id,
            'user_id' => $student->id,
            'status' => ExamAttempt::STATUS_ACTIVE,
            'started_at' => now()->subMinutes(10),
        ]);

        $studentExam = Exam::create([
            'title' => 'Student Start Matrix Exam',
            'start_at' => now()->subMinutes(30),
            'end_at' => now()->addMinutes(30),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_RUNNING,
            'created_by' => $admin->id,
        ]);

        $roles = [
            User::ROLE_ADMIN => $admin,
            User::ROLE_OPERATOR => $operator,
            User::ROLE_TEACHER => $teacherAssigned,
            'teacher_unassigned' => $teacherUnassigned,
            User::ROLE_STUDENT => $student,
        ];

        $this->post(route('admin.exams.publish', $publishableExam))->assertRedirect(route('login'));
        $this->post(route('operator.exams.force-submit', $operatorAttempt), ['reason' => 'Matrix test reason.'])->assertRedirect(route('login'));
        $this->post(route('student.exams.start', $studentExam))->assertRedirect(route('login'));

        foreach ($roles as $key => $user) {
            $publishResponse = $this->actingAs($user)->post(route('admin.exams.publish', $publishableExam));
            if ($key === User::ROLE_ADMIN) {
                $this->assertNotSame(403, $publishResponse->getStatusCode(), 'Admin should be allowed to publish draft exam.');
            } else {
                $publishResponse->assertStatus(403);
            }

            $forceSubmitResponse = $this->actingAs($user)->post(route('operator.exams.force-submit', $operatorAttempt), [
                'reason' => 'Matrix test reason.',
            ]);
            if ($key === User::ROLE_OPERATOR) {
                $this->assertNotSame(403, $forceSubmitResponse->getStatusCode(), 'Operator should be allowed to force submit.');
            } else {
                $forceSubmitResponse->assertStatus(403);
            }

            $startResponse = $this->actingAs($user)->post(route('student.exams.start', $studentExam));
            if ($key === User::ROLE_STUDENT) {
                $this->assertNotSame(403, $startResponse->getStatusCode(), 'Student should be allowed to start exam.');
            } else {
                $startResponse->assertStatus(403);
            }
        }
    }

    private function seedRoleContext(): array
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $operator = User::factory()->create(['role' => User::ROLE_OPERATOR]);
        $teacherAssigned = User::factory()->create(['role' => User::ROLE_TEACHER]);
        $teacherUnassigned = User::factory()->create(['role' => User::ROLE_TEACHER]);

        $year = SchoolYear::create([
            'name' => '2025/2026',
            'is_active' => true,
        ]);
        $class = SchoolClass::create([
            'name' => '7A',
            'grade_level' => 7,
            'school_year_id' => $year->id,
        ]);

        HomeroomTeacher::create([
            'teacher_id' => $teacherAssigned->id,
            'class_id' => $class->id,
        ]);

        $student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
            'class_id' => $class->id,
            'nis' => 'S-0001',
        ]);

        return [$admin, $operator, $teacherAssigned, $teacherUnassigned, $student];
    }
}
