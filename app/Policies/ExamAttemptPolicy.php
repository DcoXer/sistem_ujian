<?php

namespace App\Policies;

use App\Models\ExamAttempt;
use App\Models\User;

class ExamAttemptPolicy
{
    public function view(User $user, ExamAttempt $attempt): bool
    {
        if (in_array($user->role, [User::ROLE_ADMIN, User::ROLE_OPERATOR], true)) {
            return true;
        }

        return $user->role === User::ROLE_PESERTA && (int) $attempt->user_id === (int) $user->id;
    }

    public function manualScore(User $user, ExamAttempt $attempt): bool
    {
        if (! in_array($user->role, [User::ROLE_ADMIN, User::ROLE_OPERATOR], true)) {
            return false;
        }

        return in_array($attempt->status, [ExamAttempt::STATUS_SUBMITTED, ExamAttempt::STATUS_FINISHED], true);
    }

    public function answer(User $user, ExamAttempt $attempt): bool
    {
        if ($user->role !== User::ROLE_PESERTA || (int) $attempt->user_id !== (int) $user->id) {
            return false;
        }

        if ($attempt->status !== ExamAttempt::STATUS_ACTIVE || $attempt->answers_locked_at !== null) {
            return false;
        }

        $exam = $attempt->exam;
        if (! $exam || $exam->status !== \App\Models\Exam::STATUS_RUNNING) {
            return false;
        }

        $now = now();
        return ! $now->lt($exam->start_at) && ! $now->gt($exam->end_at);
    }

    public function submit(User $user, ExamAttempt $attempt): bool
    {
        if ($user->role !== User::ROLE_PESERTA || (int) $attempt->user_id !== (int) $user->id) {
            return false;
        }

        return $attempt->status === ExamAttempt::STATUS_ACTIVE && $attempt->submitted_at === null;
    }

    public function viewResult(User $user, ExamAttempt $attempt): bool
    {
        return $this->view($user, $attempt);
    }
}
