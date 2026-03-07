<?php

namespace Tests\Feature\Exam;

use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MaliciousAuthorTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_cannot_escalate_to_admin_publish_and_user_management(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $author = User::factory()->create(['role' => User::ROLE_TEACHER]);

        $exam = Exam::create([
            'title' => 'Escalation Exam',
            'start_at' => now()->addHour(),
            'end_at' => now()->addHours(2),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_DRAFT,
            'created_by' => $admin->id,
            'author_id' => $author->id,
        ]);

        $this->actingAs($author)
            ->post(route('admin.exams.publish', $exam))
            ->assertForbidden();

        $this->actingAs($author)
            ->post(route('admin.users.store'), [
                'name' => 'Pwned',
                'email' => 'pwned@example.com',
                'role' => User::ROLE_ADMIN,
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertForbidden();
    }

    public function test_author_cannot_create_exam_even_with_forged_author_id_payload(): void
    {
        $author = User::factory()->create(['role' => User::ROLE_TEACHER]);
        $otherAuthor = User::factory()->create(['role' => User::ROLE_TEACHER]);

        $this->actingAs($author)
            ->post(route('admin.exams.store'), [
                'title' => 'Forged Exam',
                'start_at' => now()->addHour()->toDateTimeString(),
                'end_at' => now()->addHours(2)->toDateTimeString(),
                'duration_minutes' => 60,
                'author_id' => $otherAuthor->id,
            ])
            ->assertForbidden();
    }

    public function test_exam_question_ownership_is_exam_only_not_author_column(): void
    {
        $this->assertTrue(method_exists(ExamQuestion::class, 'exam'));
        $this->assertFalse(method_exists(ExamQuestion::class, 'author'));
        $this->assertFalse(Schema::hasColumn('exam_questions', 'author_id'));
    }
}


