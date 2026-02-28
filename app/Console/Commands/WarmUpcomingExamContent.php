<?php

namespace App\Console\Commands;

use App\Models\Exam;
use App\Services\ExamContentCacheService;
use Illuminate\Console\Command;

class WarmUpcomingExamContent extends Command
{
    protected $signature = 'exams:warm-content {--window=15 : Minutes before exam start to prewarm}';

    protected $description = 'Pre-warm static exam question/option cache for upcoming running exams.';

    public function __construct(protected ExamContentCacheService $examContentCacheService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $window = max(1, (int) $this->option('window'));
        $now = now();
        $until = now()->addMinutes($window);

        $examIds = Exam::query()
            ->where('status', Exam::STATUS_RUNNING)
            ->whereNotNull('start_at')
            ->whereBetween('start_at', [$now, $until])
            ->pluck('id');

        foreach ($examIds as $examId) {
            $this->examContentCacheService->warmExamContent((int) $examId);
        }

        $this->info('Prewarmed exam content: '.$examIds->count());

        return self::SUCCESS;
    }
}

