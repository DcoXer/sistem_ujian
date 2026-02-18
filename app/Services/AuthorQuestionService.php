<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamQuestion;
use Illuminate\Support\Facades\DB;

class AuthorQuestionService
{
    /**
     * Author question orchestration service.
     * Dependency direction:
     * - owns question delete + reorder invariants for an exam.
     * - controllers must delegate domain mutation to this service.
     */
    public function deleteQuestion(Exam $exam, ExamQuestion $question): void
    {
        DB::transaction(function () use ($exam, $question) {
            $question->delete();

            $remaining = ExamQuestion::query()
                ->where('exam_id', $exam->id)
                ->orderBy('order')
                ->orderBy('id')
                ->get();

            foreach ($remaining as $index => $item) {
                $expectedOrder = $index + 1;
                if ((int) $item->order !== $expectedOrder) {
                    $item->update(['order' => $expectedOrder]);
                }
            }
        });
    }
}

