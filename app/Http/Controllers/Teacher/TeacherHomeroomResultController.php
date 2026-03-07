<?php

namespace App\Http\Controllers\Teacher;

use App\Exports\HomeroomResultsExport;
use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\HomeroomTeacher;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class TeacherHomeroomResultController extends Controller
{
    public function index(Request $request): View
    {
        return view('teacher.homeroom.results.index');
    }

    public function export(Request $request)
    {
        [$assignedClasses, $rows] = $this->resolveExportContext($request);
        $format = strtolower((string) $request->query('format', 'excel'));

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('teacher.homeroom.results.pdf', [
                'assignedClasses' => $assignedClasses,
                'rows' => $rows,
                'printedAt' => now(),
            ])->setPaper('a4', 'landscape');

            return $pdf->download('hasil-wali-kelas-'.now()->format('Ymd_His').'.pdf');
        }

        return Excel::download(new HomeroomResultsExport($rows), 'hasil-wali-kelas-'.now()->format('Ymd_His').'.xlsx');
    }

    protected function getAssignedClasses(int $teacherId): Collection
    {
        return HomeroomTeacher::query()
            ->where('teacher_id', $teacherId)
            ->with(['schoolClass.schoolYear:id,name'])
            ->get()
            ->pluck('schoolClass')
            ->filter()
            ->values();
    }

    protected function buildResultRows(array $classIds): Collection
    {
        if (empty($classIds)) {
            return collect();
        }

        return ExamAttempt::query()
            ->join('users', 'users.id', '=', 'exam_attempts.user_id')
            ->join('exams', 'exams.id', '=', 'exam_attempts.exam_id')
            ->leftJoin('school_classes', 'school_classes.id', '=', 'users.class_id')
            ->leftJoin('subjects', 'subjects.id', '=', 'exams.subject_id')
            ->whereIn('users.class_id', $classIds)
            ->where('users.role', User::ROLE_STUDENT)
            ->where('exams.status', Exam::STATUS_FINISHED)
            ->whereIn('exam_attempts.status', [ExamAttempt::STATUS_SUBMITTED, ExamAttempt::STATUS_FINISHED])
            ->orderBy('school_classes.name')
            ->orderBy('users.name')
            ->orderByDesc('exam_attempts.submitted_at')
            ->get([
                'exam_attempts.id as attempt_id',
                'exam_attempts.status as attempt_status',
                'exam_attempts.score',
                'exam_attempts.submitted_at',
                'users.id as student_id',
                'users.nis as student_nis',
                'users.name as student_name',
                'school_classes.name as class_name',
                'exams.id as exam_id',
                'exams.title as exam_title',
                'subjects.name as subject_name',
            ]);
    }

    protected function resolveExportContext(Request $request): array
    {
        $teacher = $request->user();
        $assignedClasses = $this->getAssignedClasses((int) $teacher->id);
        if ($assignedClasses->isEmpty()) {
            abort(404, 'Kelas wali tidak ditemukan.');
        }

        return [
            $assignedClasses,
            $this->buildResultRows($assignedClasses->pluck('id')->map(fn ($id) => (int) $id)->all()),
        ];
    }
}
