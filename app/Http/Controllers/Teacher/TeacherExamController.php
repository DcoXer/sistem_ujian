<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamOption;
use App\Models\ExamQuestion;
use App\Services\AuthorQuestionService;
use App\Services\ExamContentCacheService;
use App\Services\SecurityAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TeacherExamController extends Controller
{
    protected ?bool $hasQuestionImageColumn = null;

    public function __construct(
        protected SecurityAuditService $securityAuditService,
        protected AuthorQuestionService $authorQuestionService,
        protected ExamContentCacheService $examContentCacheService
    ) {
    }

    public function index(): View
    {
        return view('teacher.exams.index');
    }

    public function show(Exam $exam): View
    {
        $canManageQuestions = request()->user()->can('update', $exam);
        $canViewFinishedQuestions = request()->user()->can('viewAuthoredQuestions', $exam);

        if (! $canManageQuestions && ! $canViewFinishedQuestions) {
            abort(403);
        }

        $questionColumns = ['id', 'exam_id', 'question_text', 'points', 'order'];
        if ($this->hasQuestionImageColumn()) {
            $questionColumns[] = 'question_image_path';
        }

        $exam->load([
            'questions' => fn ($query) => $query->select($questionColumns),
            'questions.options' => fn ($query) => $query->select(['id', 'exam_question_id', 'option_text', 'is_correct']),
        ]);
        $questionsCount = $exam->questions->count();

        return view('teacher.exams.show', compact('exam', 'questionsCount', 'canManageQuestions', 'canViewFinishedQuestions'));
    }

    public function storeQuestion(Request $request, Exam $exam): RedirectResponse
    {
        $this->authorize('update', $exam);

        $maxQuestions = max(1, (int) config('exam.author_max_questions_per_exam', 100));
        $currentCount = (int) $exam->questions()->count();
        if ($currentCount >= $maxQuestions) {
            return back()
                ->withErrors(['question_limit' => "Batas maksimal soal per exam adalah {$maxQuestions}."])
                ->withInput();
        }

        $data = $request->validate([
            'question_text' => ['required', 'string', 'max:2000', 'not_regex:/<[^>]+>/'],
            'question_image' => ['nullable', 'image', 'max:3072'],
            'points' => ['required', 'integer', 'min:1'],
            'order' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('exam_questions', 'order')
                    ->where(fn ($query) => $query->where('exam_id', $exam->id)),
            ],
            'options' => ['required', 'array', 'min:2'],
            'options.*' => ['required', 'string', 'max:1000', 'not_regex:/<[^>]+>/'],
            'correct_option' => ['required', 'integer', 'min:0'],
        ]);

        $nextOrder = (int) ($exam->questions()->max('order') ?? 0) + 1;
        if ((int) $data['order'] !== $nextOrder) {
            return back()
                ->withErrors(['order' => "Urutan soal wajib berurutan. Order berikutnya harus {$nextOrder}."])
                ->withInput();
        }

        $supportsQuestionImage = $this->hasQuestionImageColumn();

        $questionImagePath = null;
        if ($supportsQuestionImage && $request->hasFile('question_image')) {
            $questionImagePath = $request->file('question_image')->store('exam-questions', 'public');
        }

        $payload = [
            'exam_id' => $exam->id,
            'question_text' => $data['question_text'],
            'points' => $data['points'],
            'order' => $data['order'],
        ];
        if ($supportsQuestionImage) {
            $payload['question_image_path'] = $questionImagePath;
        }

        $question = ExamQuestion::create($payload);

        foreach ($data['options'] as $index => $optionText) {
            $question->options()->create([
                'option_text' => $optionText,
                'is_correct' => (int) $data['correct_option'] === (int) $index,
            ]);
        }
        $this->examContentCacheService->invalidateExamContent((int) $exam->id);

        $this->securityAuditService->log($request, 'question_created', $question, [
            'exam_id' => $exam->id,
            'order' => $question->order,
            'points' => $question->points,
        ]);

        return back()->with('status', 'question-added');
    }

    public function updateQuestion(Request $request, Exam $exam, ExamQuestion $question): RedirectResponse
    {
        $this->authorize('update', $exam);

        if ((int) $question->exam_id !== (int) $exam->id) {
            abort(404);
        }

        $data = $request->validate([
            'question_text' => ['required', 'string', 'max:2000', 'not_regex:/<[^>]+>/'],
            'question_image' => ['nullable', 'image', 'max:3072'],
            'points' => ['required', 'integer', 'min:1'],
            'order' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('exam_questions', 'order')
                    ->where(fn ($query) => $query->where('exam_id', $exam->id))
                    ->ignore($question->id),
            ],
            'options' => ['required', 'array', 'min:2'],
            'options.*' => ['nullable', 'string', 'max:1000', 'not_regex:/<[^>]+>/'],
            'correct_option' => ['required', 'integer', 'min:0'],
        ]);

        if ((int) $data['order'] !== (int) $question->order) {
            return back()
                ->withErrors(['order' => 'Urutan soal tidak boleh diubah setelah soal dibuat.'])
                ->withInput();
        }

        $maxReorder = (int) $exam->questions()->count();
        if ((int) $data['order'] > $maxReorder) {
            return back()
                ->withErrors(['order' => "Order tidak valid. Maksimal order saat ini adalah {$maxReorder}."])
                ->withInput();
        }

        $normalizedOptions = collect($data['options'])
            ->map(fn ($value) => trim((string) $value))
            ->all();

        $nonEmptyOptions = collect($normalizedOptions)
            ->filter(fn ($value) => $value !== '')
            ->values()
            ->all();

        if (count($nonEmptyOptions) < 2) {
            return back()->withErrors(['options' => 'Minimal 2 opsi jawaban harus diisi.'])->withInput();
        }

        $correctOptionIndex = (int) $data['correct_option'];
        if (! array_key_exists($correctOptionIndex, $normalizedOptions) || $normalizedOptions[$correctOptionIndex] === '') {
            return back()->withErrors(['correct_option' => 'Index jawaban benar harus menunjuk ke opsi yang terisi.'])->withInput();
        }

        $supportsQuestionImage = $this->hasQuestionImageColumn();
        $questionImagePath = $question->question_image_path;
        if ($supportsQuestionImage && $request->hasFile('question_image')) {
            $newPath = $request->file('question_image')->store('exam-questions', 'public');
            if ($questionImagePath) {
                Storage::disk('public')->delete($questionImagePath);
            }
            $questionImagePath = $newPath;
        }

        $payload = [
            'question_text' => $data['question_text'],
            'points' => $data['points'],
            'order' => $data['order'],
        ];
        if ($supportsQuestionImage) {
            $payload['question_image_path'] = $questionImagePath;
        }

        $question->update($payload);

        $question->options()->delete();

        foreach ($normalizedOptions as $index => $optionText) {
            if ($optionText === '') {
                continue;
            }

            ExamOption::create([
                'exam_question_id' => $question->id,
                'option_text' => $optionText,
                'is_correct' => $correctOptionIndex === $index,
            ]);
        }
        $this->examContentCacheService->invalidateExamContent((int) $exam->id);

        $this->securityAuditService->log($request, 'question_updated', $question, [
            'exam_id' => $exam->id,
            'order' => $question->order,
            'points' => $question->points,
        ]);

        return back()->with('status', 'question-updated');
    }

    public function destroyQuestion(Request $request, Exam $exam, ExamQuestion $question): RedirectResponse
    {
        $this->authorize('update', $exam);

        if ((int) $question->exam_id !== (int) $exam->id) {
            abort(404);
        }

        if ($exam->attempts()->exists()) {
            return back()->withErrors([
                'question_delete' => 'Soal tidak bisa dihapus karena exam sudah memiliki data attempt peserta.',
            ]);
        }

        $questionSnapshot = [
            'exam_id' => $exam->id,
            'question_id' => $question->id,
            'order' => $question->order,
            'points' => $question->points,
        ];

        if ($this->hasQuestionImageColumn() && $question->question_image_path) {
            Storage::disk('public')->delete($question->question_image_path);
        }

        $this->authorQuestionService->deleteQuestion($exam, $question);
        $this->examContentCacheService->invalidateExamContent((int) $exam->id);

        $this->securityAuditService->log($request, 'question_deleted', null, $questionSnapshot);

        return back()->with('status', 'question-deleted');
    }

    protected function hasQuestionImageColumn(): bool
    {
        if ($this->hasQuestionImageColumn !== null) {
            return $this->hasQuestionImageColumn;
        }

        return $this->hasQuestionImageColumn = Schema::hasColumn('exam_questions', 'question_image_path');
    }
}
