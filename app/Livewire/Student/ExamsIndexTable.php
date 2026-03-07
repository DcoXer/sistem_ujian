<?php

namespace App\Livewire\Student;

use App\Exceptions\StateConflictException;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Services\ExamLifecycleService;
use App\Services\ExamParticipationService;
use App\Services\SecurityAuditService;
use App\Support\ExamUiAction;
use App\Support\ExamUiState;
use Illuminate\Support\Collection;
use Livewire\Component;

class ExamsIndexTable extends Component
{
    public function startExam(int $examId)
    {
        $exam = Exam::query()->findOrFail($examId);

        try {
            $attempt = app(ExamParticipationService::class)->startExam(auth()->user(), $exam);
        } catch (StateConflictException $exception) {
            $this->addError('start', $exception->getMessage());
            return null;
        }

        app(SecurityAuditService::class)->log(request(), 'attempt_started', $attempt, [
            'exam_id' => $exam->id,
        ]);

        return redirect()->route('student.exams.show', $attempt);
    }

    public function render()
    {
        app(ExamLifecycleService::class)->closeExpiredExams();

        $user = auth()->user();
        $userId = $user->id;
        $user->loadMissing('schoolClass:id,school_year_id,grade_level');
        $participantClassId = $user->class_id ? (int) $user->class_id : null;
        $participantSchoolYearId = $user->schoolClass?->school_year_id ? (int) $user->schoolClass->school_year_id : null;
        $participantGradeLevel = $user->schoolClass?->grade_level ? (int) $user->schoolClass->grade_level : null;

        $exams = Exam::query()
            ->where(function ($query) use ($userId, $participantClassId, $participantSchoolYearId, $participantGradeLevel) {
                $query->where(function ($runningQuery) use ($participantClassId, $participantSchoolYearId, $participantGradeLevel) {
                    $runningQuery->where('status', Exam::STATUS_RUNNING);
                    $this->applyStudentExamDomainScope($runningQuery, $participantClassId, $participantSchoolYearId, $participantGradeLevel);
                })
                    ->orWhere(function ($finishedQuery) use ($userId) {
                        $finishedQuery->where('status', Exam::STATUS_FINISHED)
                            ->whereHas('attempts', fn ($attemptQuery) => $attemptQuery->where('user_id', $userId));
                    });
            })
            ->with(['attempts' => fn ($q) => $q->where('user_id', $userId)])
            ->latest('start_at')
            ->get();

        return view('livewire.student.exams-index-table', [
            'exams' => $this->decorateExamCards($exams),
        ]);
    }

    protected function decorateExamCards(Collection $exams): Collection
    {
        return $exams->map(function (Exam $exam) {
            $attempt = $exam->attempts->first();

            $exam->my_attempt_status = $attempt?->status;
            $exam->my_attempt_id = $attempt?->id;

            $uiState = $this->resolveExamUiState($exam, $attempt);
            $exam->ui_state = $uiState['state'];
            $exam->ui_action = $uiState['action'];
            $exam->ui_message = $uiState['message'];
            $exam->ui_message_tone = $uiState['message_tone'];

            return $exam;
        });
    }

    protected function resolveExamUiState(Exam $exam, ?ExamAttempt $attempt): array
    {
        $now = now();
        $state = $exam->status === Exam::STATUS_FINISHED ? ExamUiState::FINISHED : ExamUiState::RUNNING;
        if ($exam->status === Exam::STATUS_RUNNING && $exam->start_at && $now->lt($exam->start_at)) {
            $state = ExamUiState::NOT_STARTED;
        }

        if (in_array($attempt?->status, [ExamAttempt::STATUS_SUBMITTED, ExamAttempt::STATUS_FINISHED], true)) {
            if ($exam->status === Exam::STATUS_FINISHED) {
                return [
                    'state' => $state,
                    'action' => ExamUiAction::RESULT,
                    'message' => '',
                    'message_tone' => '',
                ];
            }

            return [
                'state' => $state,
                'action' => ExamUiAction::WAITING_RESULT,
                'message' => 'Jawaban sudah terkirim. Hasil akan tampil setelah ujian selesai.',
                'message_tone' => 'amber',
            ];
        }

        if ($attempt?->status === ExamAttempt::STATUS_ACTIVE) {
            if ($exam->status === Exam::STATUS_RUNNING) {
                return [
                    'state' => $state,
                    'action' => ExamUiAction::CONTINUE_ENABLED,
                    'message' => '',
                    'message_tone' => '',
                ];
            }

            return [
                'state' => $state,
                'action' => ExamUiAction::CONTINUE_DISABLED,
                'message' => 'Waktu ujian sudah berakhir pada '.$exam->end_at?->format('d M Y H:i').'.',
                'message_tone' => 'rose',
            ];
        }

        if ($state === ExamUiState::NOT_STARTED) {
            return [
                'state' => $state,
                'action' => ExamUiAction::START_DISABLED,
                'message' => 'Ujian baru bisa dimulai pada '.$exam->start_at?->format('d M Y H:i').'.',
                'message_tone' => 'amber',
            ];
        }

        if ($exam->status === Exam::STATUS_FINISHED) {
            return [
                'state' => $state,
                'action' => ExamUiAction::START_DISABLED,
                'message' => 'Waktu ujian sudah berakhir pada '.$exam->end_at?->format('d M Y H:i').'.',
                'message_tone' => 'rose',
            ];
        }

        return [
            'state' => $state,
            'action' => ExamUiAction::START_ENABLED,
            'message' => '',
            'message_tone' => '',
        ];
    }

    protected function applyStudentExamDomainScope($query, ?int $participantClassId, ?int $participantSchoolYearId, ?int $participantGradeLevel): void
    {
        if ($participantClassId === null) {
            $query->whereNull('class_id');
        } else {
            $query->where(function ($classScope) use ($participantClassId) {
                $classScope->whereNull('class_id')
                    ->orWhere('class_id', $participantClassId);
            });
        }

        if ($participantSchoolYearId === null) {
            $query->whereNull('school_year_id');
            return;
        }

        $query->where(function ($yearScope) use ($participantSchoolYearId) {
            $yearScope->whereNull('school_year_id')
                ->orWhere('school_year_id', $participantSchoolYearId);
        });

        if ($participantGradeLevel === null) {
            $query->whereNull('target_grade_level');
            return;
        }

        $query->where(function ($gradeScope) use ($participantGradeLevel) {
            $gradeScope->whereNull('target_grade_level')
                ->orWhere('target_grade_level', $participantGradeLevel);
        });
    }
}
