<?php

namespace Tests\Feature\Exam;

use App\Models\Exam;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminExamDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_draft_exam(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $exam = Exam::create([
            'title' => 'Draft Exam',
            'start_at' => now()->addHour(),
            'end_at' => now()->addHours(2),
            'duration_minutes' => 30,
            'status' => Exam::STATUS_DRAFT,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.exams.destroy', $exam))
            ->assertRedirect(route('admin.exams.index'));

        $this->assertDatabaseMissing('exams', ['id' => $exam->id]);
    }

    public function test_admin_cannot_delete_running_exam(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $exam = Exam::create([
            'title' => 'Running Exam',
            'start_at' => now()->subHour(),
            'end_at' => now()->addHour(),
            'duration_minutes' => 30,
            'status' => Exam::STATUS_RUNNING,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.exams.destroy', $exam))
            ->assertForbidden();

        $this->assertDatabaseHas('exams', ['id' => $exam->id]);
    }
}

