<?php

namespace App\Console\Commands;

use App\Services\ExamLifecycleService;
use Illuminate\Console\Command;

class FinishExpiredExams extends Command
{
    public function __construct(
        protected ExamLifecycleService $examLifecycleService
    ) {
        parent::__construct();
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exams:finish-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark running exams whose end time has passed as finished.';

    public function handle(): int
    {
        $count = $this->examLifecycleService->closeExpiredExams();

        $this->info("{$count} exams transitioned to finished.");

        return 0;
    }
}
