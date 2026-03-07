<?php

namespace App\Livewire\Teacher;

use App\Models\Exam;
use Livewire\Component;
use Livewire\WithPagination;

class ExamsIndexTable extends Component
{
    use WithPagination;

    public function render()
    {
        $teacher = auth()->user();
        $exams = Exam::query()
            ->where(function ($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id)
                    ->orWhere('author_id', $teacher->id);
            })
            ->withCount(['questions', 'attempts'])
            ->latest()
            ->paginate(10);

        return view('livewire.teacher.exams-index-table', [
            'exams' => $exams,
        ]);
    }
}

