<?php

use App\Http\Controllers\Admin\AdminExamController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Author\AuthorExamController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Operator\OperatorExamMonitorController;
use App\Http\Controllers\Peserta\PesertaExamController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/dashboard', DashboardController::class)->middleware('auth')->name('dashboard');

Route::middleware(['auth', 'operator'])->group(function () {
    Route::prefix('operator')->group(function () {
        Route::get('/exams', [OperatorExamMonitorController::class, 'index'])->name('operator.exams.index');
        Route::get('/exams/{exam}', [OperatorExamMonitorController::class, 'show'])->name('operator.exams.show');
        Route::post('/exam-attempts/{attempt}/manual-score', [OperatorExamMonitorController::class, 'manualScore'])
            ->middleware(['can:manualScore,attempt', 'manual_score_intent'])
            ->name('operator.exams.manual-score');
        Route::post('/exam-attempts/{attempt}/force-submit', [OperatorExamMonitorController::class, 'forceSubmit'])->name('operator.exams.force-submit');
        Route::post('/exam-attempts/{attempt}/reopen', [OperatorExamMonitorController::class, 'reopen'])->name('operator.exams.reopen');
        Route::post('/exam-attempts/{attempt}/mark-issue', [OperatorExamMonitorController::class, 'markIssue'])->name('operator.exams.mark-issue');

    });
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard/realtime', [DashboardController::class, 'adminRealtime'])->name('dashboard.realtime');
    Route::get('/exams', [AdminExamController::class, 'index'])->name('exams.index');
    Route::get('/exams/create', [AdminExamController::class, 'create'])->name('exams.create');
    Route::post('/exams', [AdminExamController::class, 'store'])->name('exams.store');
    Route::get('/exams/{exam}', [AdminExamController::class, 'show'])->name('exams.show');
    Route::delete('/exams/{exam}', [AdminExamController::class, 'destroy'])->name('exams.destroy');
    Route::post('/exams/{exam}/publish', [AdminExamController::class, 'publish'])->name('exams.publish');

    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
});

Route::middleware(['auth', 'author'])->prefix('author')->name('author.')->group(function () {
    Route::get('/exams', [AuthorExamController::class, 'index'])->name('exams.index');
    Route::get('/exams/{exam}', [AuthorExamController::class, 'show'])->name('exams.show');
    Route::post('/exams/{exam}/questions', [AuthorExamController::class, 'storeQuestion'])
        ->middleware('throttle:30,1')
        ->name('exams.questions.store');
    Route::put('/exams/{exam}/questions/{question}', [AuthorExamController::class, 'updateQuestion'])
        ->middleware('throttle:30,1')
        ->name('exams.questions.update');
    Route::delete('/exams/{exam}/questions/{question}', [AuthorExamController::class, 'destroyQuestion'])
        ->middleware('throttle:30,1')
        ->name('exams.questions.destroy');
});

Route::middleware(['auth', 'peserta'])->prefix('peserta')->name('peserta.')->group(function () {
    Route::get('/exams', [PesertaExamController::class, 'index'])->name('exams.index');
    Route::get('/exams/realtime-state', [PesertaExamController::class, 'realtimeState'])->name('exams.realtime-state');
    Route::post('/exams/{exam}/start', [PesertaExamController::class, 'start'])
        ->middleware('throttle:10,1')
        ->name('exams.start');
    Route::get('/exam-attempts/{attempt}', [PesertaExamController::class, 'show'])->name('exams.show');
    Route::post('/exam-attempts/{attempt}/answer', [PesertaExamController::class, 'saveAnswer'])
        ->middleware('throttle:120,1')
        ->name('exams.answer');
    Route::get('/exam-attempts/{attempt}/timer', [PesertaExamController::class, 'timer'])->name('exams.timer');
    Route::post('/exam-attempts/{attempt}/submit', [PesertaExamController::class, 'submit'])
        ->middleware('throttle:5,1')
        ->name('exams.submit');
    Route::get('/exam-attempts/{attempt}/result', [PesertaExamController::class, 'result'])->name('exams.result');
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
