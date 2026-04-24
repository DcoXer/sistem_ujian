<?php

namespace App\Policies;

use App\Models\Exam;
use App\Models\TeacherSubject;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ExamPolicy
{
    public function create(User $user): bool
    {
        return $user->role === User::ROLE_ADMIN;
    }

    public function update(User $user, Exam $exam): bool
    {
        if ($user->role !== User::ROLE_TEACHER) {
            return false;
        }

        $teacherId = (int) ($exam->teacher_id ?? $exam->author_id ?? 0);

        if (! (
            $exam->status === Exam::STATUS_DRAFT
            && $exam->isWithinAuthoringWindow()
            && $teacherId === (int) $user->id
        )) {
            return false;
        }

        if (! $exam->subject_id) {
            return true;
        }

        // Check class-specific assignment or grade-level assignment
        return TeacherSubject::query()
            ->where('teacher_id', $user->id)
            ->where('subject_id', $exam->subject_id)
            ->where(function ($q) use ($exam) {
                $q->where('class_id', $exam->class_id)
                    ->orWhere('grade_level', $exam->target_grade_level);
            })
            ->exists();
    }

    public function publish(User $user, Exam $exam): bool
    {
        return $user->role === User::ROLE_ADMIN
            && $exam->status === Exam::STATUS_DRAFT
            && $exam->isAuthoringWindowClosed();
    }

    public function viewAuthoredQuestions(User $user, Exam $exam): bool
    {
        return $user->role === User::ROLE_TEACHER
            && $exam->status === Exam::STATUS_FINISHED
            && (int) ($exam->teacher_id ?? $exam->author_id ?? 0) === (int) $user->id;
    }

    /**
     * Admin can edit exam metadata (title, dates, type) while it's still draft.
     */
    public function updateMeta(User $user, Exam $exam): bool
    {
        return $user->role === User::ROLE_ADMIN && $exam->status === Exam::STATUS_DRAFT;
    }

    public function delete(User $user, Exam $exam): bool
    {
        return $user->role === User::ROLE_ADMIN && $exam->status === Exam::STATUS_DRAFT;
    }

    public function viewResults(User $user): bool
    {
        return in_array($user->role, [User::ROLE_ADMIN, User::ROLE_OPERATOR], true);
    }

    public function start(User $user, Exam $exam): Response
    {
        if ($user->role !== User::ROLE_STUDENT) {
            return Response::deny('Hanya student yang bisa mulai ujian.');
        }

        if ($exam->status !== Exam::STATUS_RUNNING) {
            return Response::deny('Ujian belum berjalan.');
        }

        $now = now();
        if ($now->lt($exam->start_at) || $now->gt($exam->end_at)) {
            return Response::deny('Ujian tidak dalam rentang waktu aktif.');
        }

        if (! $exam->isEligibleForPeserta($user)) {
            return Response::deny('Ujian tidak tersedia untuk kelas atau tahun ajaran kamu.');
        }

        return Response::allow();
    }
}
