<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamOption;
use App\Models\ExamQuestion;
use App\Services\ExamContentCacheService;
use App\Services\AuthorQuestionService;
use App\Services\SecurityAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuthorExamController extends Controller
{
    public function __construct(
        protected SecurityAuditService $securityAuditService,
        protected AuthorQuestionService $authorQuestionService,
        protected ExamContentCacheService $examContentCacheService
    ) {
    }

    public function index(): View
    {
        $author = request()->user();

        $exams = Exam::query()
            ->where('author_id', $author->id)
            ->withCount(['questions', 'attempts'])
            ->latest()
            ->paginate(10);

        return view('author.exams.index', compact('exams'));
    }

    public function show(Exam $exam): View
    {
        $canManageQuestions = request()->user()->can('update', $exam);
        $canViewFinishedQuestions = request()->user()->can('viewAuthoredQuestions', $exam);

        if (! $canManageQuestions && ! $canViewFinishedQuestions) {
            abort(403);
        }

        $exam->load(['questions.options', 'attempts.user']);
        $questionsCount = $exam->questions->count();

        return view('author.exams.show', compact('exam', 'questionsCount', 'canManageQuestions', 'canViewFinishedQuestions'));
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

        $question = ExamQuestion::create([
            'exam_id' => $exam->id,
            'question_text' => $data['question_text'],
            'points' => $data['points'],
            'order' => $data['order'],
        ]);

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

        $question->update([
            'question_text' => $data['question_text'],
            'points' => $data['points'],
            'order' => $data['order'],
        ]);

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

        $this->authorQuestionService->deleteQuestion($exam, $question);
        $this->examContentCacheService->invalidateExamContent((int) $exam->id);

        $this->securityAuditService->log($request, 'question_deleted', null, $questionSnapshot);

        return back()->with('status', 'question-deleted');
    }
}
