<?php

namespace Tests\Feature\Exam;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttemptAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_peserta_cannot_access_other_users_attempt(): void
    {
        Carbon::setTestNow(now());

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $owner = User::factory()->create(['role' => User::ROLE_PESERTA]);
        $otherPeserta = User::factory()->create(['role' => User::ROLE_PESERTA]);

        $exam = Exam::create([
            'title' => 'Attempt Authorization Exam',
            'start_at' => now()->subMinutes(5),
            'end_at' => now()->addMinutes(30),
            'duration_minutes' => 30,
            'status' => Exam::STATUS_RUNNING,
            'created_by' => $admin->id,
        ]);

        $attempt = ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $owner->id,
            'status' => ExamAttempt::STATUS_ACTIVE,
            'started_at' => now()->subMinute(),
        ]);

        $this->actingAs($otherPeserta)
            ->get(route('peserta.exams.show', $attempt))
            ->assertForbidden();

        $this->actingAs($otherPeserta)
            ->post(route('peserta.exams.submit', $attempt))
            ->assertForbidden();
    }
}
