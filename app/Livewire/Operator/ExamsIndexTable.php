<?php

namespace App\Livewire\Operator;

use App\Models\Exam;
use App\Services\ExamLifecycleService;
use Livewire\Component;

class ExamsIndexTable extends Component
{
    public function render()
    {
        app(ExamLifecycleService::class)->closeExpiredExams();

        $now = now();
        $exams = Exam::query()
            ->where('status', Exam::STATUS_RUNNING)
            ->withCount([
                'attempts as participants_started' => fn ($q) => $q->whereIn('status', ['active', 'submitted', 'finished']),
                'attempts as participants_in_progress' => fn ($q) => $q->where('status', 'active'),
                'attempts as participants_submitted' => fn ($q) => $q->whereIn('status', ['submitted', 'finished']),
            ])
            ->orderByRaw(
                'CASE WHEN start_at <= ? AND end_at >= ? THEN 0 WHEN start_at > ? THEN 1 ELSE 2 END',
                [$now, $now, $now]
            )
            ->orderBy('start_at')
            ->get();

        $exams->each(function (Exam $exam) use ($now) {
            $exam->phase = $now->lt($exam->start_at) ? 'upcoming' : 'active';
        });

        return view('livewire.operator.exams-index-table', [
            'exams' => $exams,
        ]);
    }
}

