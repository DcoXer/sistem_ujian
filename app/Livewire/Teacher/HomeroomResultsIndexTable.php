<?php

namespace App\Livewire\Teacher;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\HomeroomTeacher;
use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Component;

class HomeroomResultsIndexTable extends Component
{
    public string $exportFormat = 'excel';

    public function export()
    {
        return redirect()->route('teacher.homeroom.results.export', [
            'format' => $this->exportFormat,
        ]);
    }

    public function render()
    {
        $teacherId = (int) auth()->id();
        $assignedClasses = $this->getAssignedClasses($teacherId);
        $rows = $this->buildResultRows($assignedClasses->pluck('id')->map(fn ($id) => (int) $id)->all());

        $summaryByStudent = $rows
            ->groupBy('student_id')
            ->map(function ($items) {
                $first = $items->first();
                $scores = $items->pluck('score')->filter(fn ($score) => $score !== null);

                return (object) [
                    'student_id' => $first->student_id,
                    'student_nis' => $first->student_nis,
                    'student_name' => $first->student_name,
                    'exams_count' => $items->count(),
                    'avg_score' => $scores->count() > 0 ? round((float) $scores->avg(), 2) : null,
                    'max_score' => $scores->count() > 0 ? (float) $scores->max() : null,
                ];
            })
            ->sortBy('student_name')
            ->values();

        return view('livewire.teacher.homeroom-results-index-table', [
            'assignedClasses' => $assignedClasses,
            'rows' => $rows,
            'summaryByStudent' => $summaryByStudent,
        ]);
    }

    protected function getAssignedClasses(int $teacherId): Collection
    {
        return HomeroomTeacher::query()
            ->where('teacher_id', $teacherId)
            ->with(['schoolClass.schoolYear:id,name'])
            ->get()
            ->pluck('schoolClass')
            ->filter()
            ->values();
    }

    protected function buildResultRows(array $classIds): Collection
    {
        if (empty($classIds)) {
            return collect();
        }

        return ExamAttempt::query()
            ->join('users', 'users.id', '=', 'exam_attempts.user_id')
            ->join('exams', 'exams.id', '=', 'exam_attempts.exam_id')
            ->leftJoin('school_classes', 'school_classes.id', '=', 'users.class_id')
            ->leftJoin('subjects', 'subjects.id', '=', 'exams.subject_id')
            ->whereIn('users.class_id', $classIds)
            ->where('users.role', User::ROLE_STUDENT)
            ->where('exams.status', Exam::STATUS_FINISHED)
            ->whereIn('exam_attempts.status', [ExamAttempt::STATUS_SUBMITTED, ExamAttempt::STATUS_FINISHED])
            ->orderBy('school_classes.name')
            ->orderBy('users.name')
            ->orderByDesc('exam_attempts.submitted_at')
            ->get([
                'exam_attempts.id as attempt_id',
                'exam_attempts.status as attempt_status',
                'exam_attempts.score',
                'exam_attempts.submitted_at',
                'users.id as student_id',
                'users.nis as student_nis',
                'users.name as student_name',
                'school_classes.name as class_name',
                'exams.id as exam_id',
                'exams.title as exam_title',
                'subjects.name as subject_name',
            ]);
    }
}

