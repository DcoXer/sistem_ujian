<?php

namespace App\Providers;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\User;
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

        Gate::define('manage-exams', fn (User $user): bool => $user->role === User::ROLE_ADMIN);
        Gate::define('manage-users', fn (User $user): bool => $user->role === User::ROLE_ADMIN);
        Gate::define('manage-academics', fn (User $user): bool => $user->role === User::ROLE_ADMIN);
        Gate::define('monitor-exams', fn (User $user): bool => in_array($user->role, [User::ROLE_ADMIN, User::ROLE_OPERATOR], true));
        Gate::define('author-exams', fn (User $user): bool => $user->role === User::ROLE_TEACHER);
        Gate::define('view-homeroom-results', fn (User $user): bool => $user->role === User::ROLE_TEACHER && $user->homeroomOfClasses()->exists());
        Gate::define('take-exams', fn (User $user): bool => $user->role === User::ROLE_STUDENT);
    }
}
