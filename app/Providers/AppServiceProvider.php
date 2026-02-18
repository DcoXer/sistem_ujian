<?php

namespace App\Providers;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Policies\ExamAttemptPolicy;
use App\Policies\ExamPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Exam::class, ExamPolicy::class);
        Gate::policy(ExamAttempt::class, ExamAttemptPolicy::class);
    }
}
