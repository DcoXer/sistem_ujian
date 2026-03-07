<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\SchoolClass;
use App\Services\ExamParticipationService;
use App\Services\ExamLifecycleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected ExamParticipationService $participationService,
        protected ExamLifecycleService $examLifecycleService
    ) {
    }

    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $role = $user->role ?? User::ROLE_STUDENT;

        $this->examLifecycleService->closeExpiredExams();

        $user->loadMissing('schoolClass:id,school_year_id,grade_level');
        $participantClassId = $user->class_id ? (int) $user->class_id : null;
        $participantSchoolYearId = $user->schoolClass?->school_year_id ? (int) $user->schoolClass->school_year_id : null;
        $participantGradeLevel = $user->schoolClass?->grade_level ? (int) $user->schoolClass->grade_level : null;

        $activeExam = Exam::query()
            ->where('status', Exam::STATUS_RUNNING)
            ->where('start_at', '<=', now())
            ->where('end_at', '>=', now())
            ->when($role === User::ROLE_STUDENT, function ($query) use ($participantClassId, $participantSchoolYearId, $participantGradeLevel) {
                $this->applyPesertaExamDomainScope($query, $participantClassId, $participantSchoolYearId, $participantGradeLevel);
            })
            ->latest('start_at')
            ->first();

        $activeExamExpiredMessage = null;
        $upcomingExam = Exam::query()
            ->where('status', Exam::STATUS_RUNNING)
            ->where('start_at', '>', now())
            ->when($role === User::ROLE_STUDENT, function ($query) use ($participantClassId, $participantSchoolYearId, $participantGradeLevel) {
                $this->applyPesertaExamDomainScope($query, $participantClassId, $participantSchoolYearId, $participantGradeLevel);
            })
            ->orderBy('start_at')
            ->first();

        $latestExams = Exam::query()
            ->select('id', 'title', 'status', 'start_at', 'end_at', 'updated_at')
            ->latest('updated_at')
            ->limit(3)
            ->get();

        $stats = [
            ['label' => 'Exams', 'value' => DB::table('exams')->count()],
            ['label' => 'Exam Questions', 'value' => DB::table('exam_questions')->count()],
            ['label' => 'Participants', 'value' => DB::table('users')->where('role', User::ROLE_STUDENT)->count()],
            ['label' => 'Attempts', 'value' => DB::table('exam_attempts')->count()],
            ['label' => 'Submissions', 'value' => DB::table('exam_attempts')->whereIn('status', [ExamAttempt::STATUS_SUBMITTED, ExamAttempt::STATUS_FINISHED])->count()],
        ];

        $operators = DB::table('users')
            ->select('id', 'name', 'email')
            ->where('role', User::ROLE_OPERATOR)
            ->orderBy('name')
            ->limit(8)
            ->get();

        $pesertaUsers = DB::table('users')
            ->select('id', 'name', 'email')
            ->where('role', User::ROLE_STUDENT)
            ->orderBy('name')
            ->limit(12)
            ->get();

        $operatorClasses = collect();
        if ($role === User::ROLE_OPERATOR) {
            $operatorClasses = SchoolClass::query()
                ->withCount([
                    'students as students_count' => fn ($query) => $query->where('role', User::ROLE_STUDENT),
                ])
                ->orderBy('grade_level')
                ->orderBy('name')
                ->get(['id', 'name', 'grade_level']);
        }

        $myScores = DB::table('exam_attempts')
            ->join('exams', 'exams.id', '=', 'exam_attempts.exam_id')
            ->select('exams.title as match_title', DB::raw('COALESCE(exam_attempts.score, 0) as score'))
            ->when($role === User::ROLE_STUDENT, function ($query) use ($user) {
                $query->where('exam_attempts.user_id', $user->id)
                    ->where('exams.status', Exam::STATUS_FINISHED);
            })
            ->whereIn('exam_attempts.status', [ExamAttempt::STATUS_SUBMITTED, ExamAttempt::STATUS_FINISHED])
            ->orderByDesc('score')
            ->limit(5)
            ->get();

        $myInProgressAttempt = null;
        $myLatestSubmittedAttempt = null;
        if ($role === User::ROLE_STUDENT) {
            $pesertaDashboardState = $this->participationService->resolvePesertaDashboardState($user, $activeExam);
            $activeExam = $pesertaDashboardState['activeExam'];
            $activeExamExpiredMessage = $pesertaDashboardState['activeExamExpiredMessage'];
            $myInProgressAttempt = $pesertaDashboardState['myInProgressAttempt'];
            $myLatestSubmittedAttempt = $pesertaDashboardState['myLatestSubmittedAttempt'];
        }

        $sidebarLinks = match ($role) {
            User::ROLE_OPERATOR => [
                ['label' => 'Dashboard', 'url' => route('dashboard').'#stats', 'icon' => 'home', 'section' => 'stats'],
                ['label' => 'Monitoring Ujian', 'url' => route('operator.exams.index'), 'icon' => 'play', 'section' => null],
                ['label' => 'Ujian Aktif', 'url' => route('dashboard').'#ujian-aktif', 'icon' => 'users', 'section' => 'ujian-aktif'],
                ['label' => 'Input Nilai', 'url' => route('dashboard').'#input-nilai', 'icon' => 'clipboard', 'section' => 'input-nilai'],
                ['label' => 'Profile', 'url' => route('profile.edit'), 'icon' => 'user', 'section' => null],
            ],
            User::ROLE_TEACHER => [
                ['label' => 'Dashboard', 'url' => route('dashboard').'#stats', 'icon' => 'home', 'section' => 'stats'],
                ['label' => 'Kelola Soal Ujian', 'url' => route('teacher.exams.index'), 'icon' => 'book', 'section' => null],
                ['label' => 'Profile', 'url' => route('profile.edit'), 'icon' => 'user', 'section' => null],
            ],
            User::ROLE_ADMIN => [
                ['label' => 'Dashboard', 'url' => route('dashboard').'#stats', 'icon' => 'home', 'section' => 'stats'],
                ['label' => 'Kelola Ujian', 'url' => route('admin.exams.index'), 'icon' => 'calendar', 'section' => null],
                ['label' => 'Kelola Operator', 'url' => route('dashboard').'#kelola-operator', 'icon' => 'users', 'section' => 'kelola-operator'],
                ['label' => 'Laporan Ujian', 'url' => route('dashboard').'#laporan-ujian', 'icon' => 'chart', 'section' => 'laporan-ujian'],
                ['label' => 'Profile', 'url' => route('profile.edit'), 'icon' => 'user', 'section' => null],
            ],
            default => [
                ['label' => 'Dashboard', 'url' => route('dashboard').'#stats', 'icon' => 'home', 'section' => 'stats'],
                ['label' => 'Mulai Ujian', 'url' => route('student.exams.index'), 'icon' => 'play', 'section' => null],
                ['label' => 'Jawab Soal', 'url' => route('dashboard').'#jawab-soal', 'icon' => 'clipboard', 'section' => 'jawab-soal'],
                ['label' => 'Nilai Saya', 'url' => route('dashboard').'#nilai-saya', 'icon' => 'trophy', 'section' => 'nilai-saya'],
                ['label' => 'Profile', 'url' => route('profile.edit'), 'icon' => 'user', 'section' => null],
            ],
        };

        return view('dashboard', [
            'role' => $role,
            'stats' => $stats,
            'activeExam' => $activeExam,
            'activeExamExpiredMessage' => $activeExamExpiredMessage,
            'upcomingExam' => $upcomingExam,
            'latestExams' => $latestExams,
            'operators' => $operators,
            'pesertaUsers' => $pesertaUsers,
            'operatorClasses' => $operatorClasses,
            'myScores' => $myScores,
            'myInProgressAttempt' => $myInProgressAttempt,
            'myLatestSubmittedAttempt' => $myLatestSubmittedAttempt,
            'sidebarLinks' => $sidebarLinks,
        ]);
    }

    public function adminRealtime(Request $request): \Illuminate\Http\JsonResponse
    {
        if ($request->user()?->role !== User::ROLE_ADMIN) {
            abort(403);
        }

        $this->examLifecycleService->closeExpiredExams();

        $latestExams = Exam::query()
            ->select('id', 'title', 'status', 'start_at', 'end_at', 'updated_at')
            ->latest('updated_at')
            ->limit(3)
            ->get();

        return response()->json([
            'server_now_ms' => now()->getTimestamp() * 1000,
            'latest_exams' => $latestExams,
        ]);
    }

    protected function applyPesertaExamDomainScope($query, ?int $participantClassId, ?int $participantSchoolYearId, ?int $participantGradeLevel): void
    {
        if ($participantClassId === null) {
            $query->whereNull('class_id');
        } else {
            $query->where(function ($classScope) use ($participantClassId) {
                $classScope->whereNull('class_id')
                    ->orWhere('class_id', $participantClassId);
            });
        }

        if ($participantSchoolYearId === null) {
            $query->whereNull('school_year_id');
            return;
        }

        $query->where(function ($yearScope) use ($participantSchoolYearId) {
            $yearScope->whereNull('school_year_id')
                ->orWhere('school_year_id', $participantSchoolYearId);
        });

        if ($participantGradeLevel === null) {
            $query->whereNull('target_grade_level');
            return;
        }

        $query->where(function ($gradeScope) use ($participantGradeLevel) {
            $gradeScope->whereNull('target_grade_level')
                ->orWhere('target_grade_level', $participantGradeLevel);
        });
    }
}
