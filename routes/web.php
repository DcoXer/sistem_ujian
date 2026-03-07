<?php

use App\Http\Controllers\Admin\AdminAssignmentController;
use App\Http\Controllers\Admin\AdminClassController;
use App\Http\Controllers\Admin\AdminExamController;
use App\Http\Controllers\Admin\AdminSubjectController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Operator\OperatorClassStudentController;
use App\Http\Controllers\Operator\OperatorExamMonitorController;
use App\Http\Controllers\Student\StudentExamController;
use App\Http\Controllers\Teacher\TeacherExamController;
use App\Http\Controllers\Teacher\TeacherHomeroomResultController;
use App\Http\Controllers\Teacher\TeacherHomeroomStudentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/dashboard', DashboardController::class)->middleware('auth')->name('dashboard');

Route::middleware(['auth', 'operator', 'can:monitor-exams'])->group(function () {
    Route::prefix('operator')->group(function () {
        Route::get('/exams', [OperatorExamMonitorController::class, 'index'])->name('operator.exams.index');
        Route::get('/exams/{exam}', [OperatorExamMonitorController::class, 'show'])->name('operator.exams.show');
        Route::get('/classes/{class}/students', [OperatorClassStudentController::class, 'index'])->name('operator.classes.students.index');
        Route::post('/exam-attempts/{attempt}/manual-score', [OperatorExamMonitorController::class, 'manualScore'])
            ->middleware(['can:manualScore,attempt', 'manual_score_intent'])
            ->name('operator.exams.manual-score');
        Route::post('/exam-attempts/{attempt}/force-submit', [OperatorExamMonitorController::class, 'forceSubmit'])->name('operator.exams.force-submit');
        Route::post('/exam-attempts/{attempt}/reopen', [OperatorExamMonitorController::class, 'reopen'])->name('operator.exams.reopen');
        Route::post('/exam-attempts/{attempt}/mark-issue', [OperatorExamMonitorController::class, 'markIssue'])->name('operator.exams.mark-issue');

    });
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::middleware('can:manage-exams')->group(function () {
        Route::get('/dashboard/realtime', [DashboardController::class, 'adminRealtime'])->name('dashboard.realtime');
        Route::get('/exams', [AdminExamController::class, 'index'])->name('exams.index');
        Route::get('/exams/create', [AdminExamController::class, 'create'])->name('exams.create');
        Route::post('/exams', [AdminExamController::class, 'store'])->name('exams.store');
        Route::get('/exams/{exam}', [AdminExamController::class, 'show'])->name('exams.show');
        Route::delete('/exams/{exam}', [AdminExamController::class, 'destroy'])->name('exams.destroy');
        Route::post('/exams/{exam}/publish', [AdminExamController::class, 'publish'])->name('exams.publish');
    });

    Route::middleware('can:manage-users')->group(function () {
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/students', [AdminUserController::class, 'students'])->name('users.students.index');
        Route::get('/users/teachers', [AdminUserController::class, 'teachers'])->name('users.teachers.index');
        Route::get('/users/operators', [AdminUserController::class, 'operators'])->name('users.operators.index');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('/users/export', [AdminUserController::class, 'export'])->name('users.export');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    });

    Route::middleware('can:manage-academics')->group(function () {
        Route::get('/classes', [AdminClassController::class, 'index'])->name('classes.index');
        Route::post('/classes', [AdminClassController::class, 'store'])->name('classes.store');
        Route::post('/school-years', [AdminClassController::class, 'storeSchoolYear'])->name('school-years.store');
        Route::post('/school-years/{schoolYear}/activate', [AdminClassController::class, 'activateSchoolYear'])->name('school-years.activate');
        Route::put('/classes/{class}', [AdminClassController::class, 'update'])->name('classes.update');
        Route::post('/classes/{class}/students/sync', [AdminClassController::class, 'syncStudents'])->name('classes.students.sync');
        Route::get('/classes/students/template', [AdminClassController::class, 'downloadStudentImportTemplate'])->name('classes.students.template');
        Route::get('/subjects', [AdminSubjectController::class, 'index'])->name('subjects.index');

        Route::get('/assignments', [AdminAssignmentController::class, 'index'])->name('assignments.index');
        Route::post('/assignments/teacher-subjects', [AdminAssignmentController::class, 'storeTeacherSubject'])->name('assignments.teacher-subjects.store');
        Route::delete('/assignments/teacher-subjects/{teacherSubject}', [AdminAssignmentController::class, 'destroyTeacherSubject'])->name('assignments.teacher-subjects.destroy');
        Route::post('/assignments/homerooms', [AdminAssignmentController::class, 'storeHomeroom'])->name('assignments.homerooms.store');
        Route::delete('/assignments/homerooms/{homeroomTeacher}', [AdminAssignmentController::class, 'destroyHomeroom'])->name('assignments.homerooms.destroy');
    });
});

Route::middleware(['auth', 'teacher', 'can:author-exams'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/exams', [TeacherExamController::class, 'index'])->name('exams.index');
    Route::get('/exams/{exam}', [TeacherExamController::class, 'show'])->name('exams.show');
    Route::post('/exams/{exam}/questions', [TeacherExamController::class, 'storeQuestion'])
        ->middleware('throttle:30,1')
        ->name('exams.questions.store');
    Route::put('/exams/{exam}/questions/{question}', [TeacherExamController::class, 'updateQuestion'])
        ->middleware('throttle:30,1')
        ->name('exams.questions.update');
    Route::delete('/exams/{exam}/questions/{question}', [TeacherExamController::class, 'destroyQuestion'])
        ->middleware('throttle:30,1')
        ->name('exams.questions.destroy');
});

Route::middleware(['auth', 'teacher', 'can:view-homeroom-results'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/homeroom/results', [TeacherHomeroomResultController::class, 'index'])->name('homeroom.results.index');
    Route::get('/homeroom/results/export', [TeacherHomeroomResultController::class, 'export'])->name('homeroom.results.export');
    Route::get('/homeroom/students', [TeacherHomeroomStudentController::class, 'index'])->name('homeroom.students.index');
});

Route::middleware(['auth', 'student', 'can:take-exams'])->prefix('student')->name('student.')->group(function () {
    Route::get('/exams', [StudentExamController::class, 'index'])->name('exams.index');
    Route::get('/exams/realtime-state', [StudentExamController::class, 'realtimeState'])->name('exams.realtime-state');
    Route::post('/exams/{exam}/start', [StudentExamController::class, 'start'])
        ->middleware('throttle:10,1')
        ->name('exams.start');
    Route::get('/exam-attempts/{attempt}', [StudentExamController::class, 'show'])->name('exams.show');
    Route::post('/exam-attempts/{attempt}/answer', [StudentExamController::class, 'saveAnswer'])
        ->middleware('throttle:120,1')
        ->name('exams.answer');
    Route::get('/exam-attempts/{attempt}/timer', [StudentExamController::class, 'timer'])->name('exams.timer');
    Route::post('/exam-attempts/{attempt}/submit', [StudentExamController::class, 'submit'])
        ->middleware('throttle:5,1')
        ->name('exams.submit');
    Route::get('/exam-attempts/{attempt}/result', [StudentExamController::class, 'result'])->name('exams.result');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/photo/{user}', [ProfileController::class, 'showPhoto'])
        ->middleware('signed')
        ->name('profile.photo.show');
});

require __DIR__.'/auth.php';
