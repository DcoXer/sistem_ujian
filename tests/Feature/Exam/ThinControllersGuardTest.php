<?php

namespace Tests\Feature\Exam;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ThinControllersGuardTest extends TestCase
{
    public function test_controllers_do_not_execute_domain_lifecycle_primitives_directly(): void
    {
        $files = File::allFiles(app_path('Http/Controllers'));
        $violations = [];

        $forbiddenPatterns = [
            '/->canTransitionTo\(/',
            '/DB::transaction\(/',
            '/->lockForUpdate\(/',
            '/->expireAttempt\(/',
            '/->isAttemptExpired\(/',
        ];

        foreach ($files as $file) {
            $content = File::get($file->getRealPath());

            foreach ($forbiddenPatterns as $pattern) {
                if (preg_match($pattern, $content) === 1) {
                    $violations[] = str_replace('\\', '/', $file->getRelativePathname());
                    break;
                }
            }
        }

        $this->assertSame(
            [],
            $violations,
            'Controllers must stay orchestration-only. Move lifecycle/state decisions to services. Violations: '.implode(', ', $violations)
        );
    }
}

