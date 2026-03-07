<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\TeacherSubject;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class ExamConstraintService
{
    /**
     * Publishability constraints are domain invariants.
     * This service is a primitive validator: no side effects, no persistence.
     *
     * @throws AuthorizationException
     */
    public function validatePublishability(Exam $exam): void
    {
        if (! $exam->isAuthoringWindowClosed()) {
            throw new AuthorizationException('Exam belum bisa dipublish. Tunggu sampai rentang authoring selesai.');
        }

        $teacherId = (int) ($exam->teacher_id ?? $exam->author_id ?? 0);
        if ($teacherId <= 0) {
            throw new AuthorizationException('Exam harus memiliki teacher yang ditetapkan sebelum publish.');
        }

        $teacher = User::query()->find($teacherId);
        if (! $teacher || $teacher->role !== User::ROLE_TEACHER) {
            throw new AuthorizationException('Teacher exam tidak valid.');
        }

        if ($exam->subject_id && $exam->class_id) {
            $hasAssignment = TeacherSubject::query()
                ->where('teacher_id', $teacherId)
                ->where('subject_id', $exam->subject_id)
                ->where('class_id', $exam->class_id)
                ->exists();

            if (! $hasAssignment) {
                throw new AuthorizationException('Teacher belum ter-assign ke mapel dan kelas ujian ini.');
            }
        }

        $questions = $exam->questions()->with('options')->orderBy('order')->get();
        if ($questions->isEmpty()) {
            throw new AuthorizationException('Exam harus punya minimal 1 soal sebelum publish.');
        }

        $orders = $questions->pluck('order');
        if ($orders->count() !== $orders->unique()->count()) {
            throw new AuthorizationException('Urutan soal harus unik dalam satu exam.');
        }

        foreach ($questions as $question) {
            if ((int) $question->points < 1) {
                throw new AuthorizationException('Setiap soal harus memiliki points minimal 1.');
            }

            $options = $question->options;
            if ($options->count() < 2) {
                throw new AuthorizationException('Setiap soal harus memiliki minimal 2 opsi jawaban.');
            }

            $correctCount = $options->where('is_correct', true)->count();
            if ($correctCount !== 1) {
                throw new AuthorizationException('Setiap soal harus memiliki tepat 1 jawaban benar.');
            }
        }
    }
}
