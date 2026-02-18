<?php

namespace App\Services;

use App\Models\Exam;
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

        if ((int) $exam->author_id <= 0) {
            throw new AuthorizationException('Exam harus memiliki author yang ditetapkan sebelum publish.');
        }

        $author = $exam->author()->first();
        if (! $author || $author->role !== User::ROLE_AUTHOR) {
            throw new AuthorizationException('Author exam tidak valid.');
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
