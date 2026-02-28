<?php

namespace Tests\Feature\Exam;

use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\User;
use App\Services\ExamContentCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ContentCacheWarmupTest extends TestCase
{
    use RefreshDatabase;

    public function test_warm_content_command_prewarms_upcoming_running_exam(): void
    {
        config()->set('cache.default', 'array');

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $exam = Exam::create([
            'title' => 'Warmup Exam',
            'start_at' => now()->addMinutes(10),
            'end_at' => now()->addMinutes(70),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_RUNNING,
            'created_by' => $admin->id,
        ]);

        $question = ExamQuestion::create([
            'exam_id' => $exam->id,
            'question_text' => 'Cached question?',
            'points' => 10,
            'order' => 1,
        ]);
        $question->options()->createMany([
            ['option_text' => 'A', 'is_correct' => false],
            ['option_text' => 'B', 'is_correct' => true],
        ]);

        $this->artisan('exams:warm-content --window=15')
            ->assertExitCode(0);

        $cacheService = app(ExamContentCacheService::class);
        $this->assertTrue(Cache::has($cacheService->cacheKey((int) $exam->id)));
    }
}

