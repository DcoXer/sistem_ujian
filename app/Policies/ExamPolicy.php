<?php

namespace App\Policies;

use App\Models\Exam;
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
        return $user->role === User::ROLE_AUTHOR
            && $exam->status === Exam::STATUS_DRAFT
            && $exam->isWithinAuthoringWindow()
            && (int) $exam->author_id === (int) $user->id;
    }

    public function publish(User $user, Exam $exam): bool
    {
        return $user->role === User::ROLE_ADMIN
            && $exam->status === Exam::STATUS_DRAFT
            && $exam->isAuthoringWindowClosed();
    }

    public function viewAuthoredQuestions(User $user, Exam $exam): bool
    {
        return $user->role === User::ROLE_AUTHOR
            && $exam->status === Exam::STATUS_FINISHED
            && (int) $exam->author_id === (int) $user->id;
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
        if ($user->role !== User::ROLE_PESERTA) {
            return Response::deny('Hanya peserta yang bisa mulai ujian.');
        }

        if ($exam->status !== Exam::STATUS_RUNNING) {
            return Response::deny('Ujian belum berjalan.');
        }

        $now = now();
        if ($now->lt($exam->start_at) || $now->gt($exam->end_at)) {
            return Response::deny('Ujian tidak dalam rentang waktu aktif.');
        }

        return Response::allow();
    }
}
