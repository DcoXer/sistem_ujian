<?php

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamOption;
use App\Models\ExamQuestion;
use App\Models\User;
use Illuminate\Database\Seeder;

class ExamSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();
        $author = User::where('email', 'author@example.com')->first();
        $peserta = User::where('email', 'peserta@example.com')->first();

        if (! $admin || ! $author || ! $peserta) {
            return;
        }

        $publishedExam = Exam::updateOrCreate(
            ['title' => 'Tryout Matematika Dasar'],
            [
                'start_at' => now()->subHour(),
                'end_at' => now()->addHours(3),
                'duration_minutes' => 45,
                'status' => Exam::STATUS_RUNNING,
                'created_by' => $admin->id,
                'author_id' => $author->id,
            ]
        );

        $draftExam = Exam::updateOrCreate(
            ['title' => 'Tryout Bahasa Indonesia'],
            [
                'start_at' => now()->addDay(),
                'end_at' => now()->addDay()->addHours(2),
                'duration_minutes' => 60,
                'status' => Exam::STATUS_DRAFT,
                'created_by' => $admin->id,
                'author_id' => $author->id,
            ]
        );

        $questionSet = [
            [
                'order' => 1,
                'question_text' => 'Hasil dari 12 + 8 adalah?',
                'points' => 10,
                'options' => ['18', '20', '22', '24'],
                'correct_index' => 1,
            ],
            [
                'order' => 2,
                'question_text' => 'Nilai x jika 2x = 14 adalah?',
                'points' => 10,
                'options' => ['5', '6', '7', '8'],
                'correct_index' => 2,
            ],
            [
                'order' => 3,
                'question_text' => 'Luas persegi dengan sisi 6 cm adalah?',
                'points' => 10,
                'options' => ['12 cm2', '24 cm2', '30 cm2', '36 cm2'],
                'correct_index' => 3,
            ],
        ];

        foreach ($questionSet as $item) {
            $question = ExamQuestion::updateOrCreate(
                ['exam_id' => $publishedExam->id, 'order' => $item['order']],
                [
                    'question_text' => $item['question_text'],
                    'points' => $item['points'],
                ]
            );

            $question->options()->delete();
            foreach ($item['options'] as $index => $optionText) {
                ExamOption::create([
                    'exam_question_id' => $question->id,
                    'option_text' => $optionText,
                    'is_correct' => $index === $item['correct_index'],
                ]);
            }
        }

        ExamAttempt::updateOrCreate(
            ['exam_id' => $publishedExam->id, 'user_id' => $peserta->id],
            [
                'status' => ExamAttempt::STATUS_ACTIVE,
                'started_at' => now()->subMinutes(10),
                'submitted_at' => null,
                'answers_locked_at' => null,
                'scoring_processed_at' => null,
                'score' => null,
            ]
        );

        if ($draftExam->questions()->count() === 0) {
            $q = ExamQuestion::create([
                'exam_id' => $draftExam->id,
                'question_text' => 'Contoh soal draft.',
                'points' => 10,
                'order' => 1,
            ]);

            $q->options()->createMany([
                ['option_text' => 'A', 'is_correct' => true],
                ['option_text' => 'B', 'is_correct' => false],
            ]);
        }
    }
}
