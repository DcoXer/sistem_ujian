<?php

namespace App\Livewire\Operator;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\SchoolClass;
use App\Models\User;
use Livewire\Component;

class ClassStudentsTable extends Component
{
    public SchoolClass $schoolClass;

    public ?int $examId = null;

    public string $search = '';

    public function mount(int $classId): void
    {
        $this->schoolClass = SchoolClass::query()
            ->select(['id', 'name', 'grade_level', 'school_year_id'])
            ->findOrFail($classId);

        $this->examId = $this->availableExamsQuery()->value('id');
    }

    public function render()
    {
        $availableExams = $this->availableExamsQuery()
            ->with('subject:id,name')
            ->get(['id', 'title', 'subject_id', 'start_at', 'end_at']);

        if ($availableExams->isNotEmpty() && ! $availableExams->contains('id', $this->examId)) {
            $this->examId = $availableExams->first()->id;
        }

        $students = User::query()
            ->where('role', User::ROLE_STUDENT)
            ->where('class_id', $this->schoolClass->id)
            ->when($this->search !== '', function ($query) {
                $query->where(function ($searchScope) {
                    $searchScope->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%')
                        ->orWhere('nis', 'like', '%'.$this->search.'%')
                        ->orWhere('nisn', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'nis', 'nisn']);

        $attemptsByUserId = collect();
        if ($this->examId) {
            $attemptsByUserId = ExamAttempt::query()
                ->where('exam_id', $this->examId)
                ->whereIn('user_id', $students->pluck('id'))
                ->get(['id', 'user_id', 'status', 'score'])
                ->keyBy('user_id');
        }

        return view('livewire.operator.class-students-table', [
            'availableExams' => $availableExams,
            'students' => $students,
            'attemptsByUserId' => $attemptsByUserId,
        ]);
    }

    protected function availableExamsQuery()
    {
        return Exam::query()
            ->where('status', Exam::STATUS_RUNNING)
            ->where(function ($scope) {
                $scope->where('class_id', $this->schoolClass->id);

                if ($this->schoolClass->grade_level !== null) {
                    $scope->orWhere(function ($gradeScope) {
                        $gradeScope->whereNull('class_id')
                            ->where('target_grade_level', $this->schoolClass->grade_level);
                    });
                }

                $scope->orWhere(function ($globalScope) {
                    $globalScope->whereNull('class_id')
                        ->whereNull('target_grade_level');
                });
            })
            ->where(function ($yearScope) {
                $yearScope->whereNull('school_year_id');

                if ($this->schoolClass->school_year_id !== null) {
                    $yearScope->orWhere('school_year_id', $this->schoolClass->school_year_id);
                }
            })
            ->orderByDesc('start_at');
    }
}
