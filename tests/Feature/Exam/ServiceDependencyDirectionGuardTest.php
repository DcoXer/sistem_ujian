<?php

namespace Tests\Feature\Exam;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ServiceDependencyDirectionGuardTest extends TestCase
{
    public function test_service_dependency_direction_is_enforced(): void
    {
        $serviceFiles = [
            'ExamConstraintService.php',
            'ExamLifecycleService.php',
            'ExamScoringService.php',
            'ExamParticipationService.php',
            'OperatorExamService.php',
        ];

        $rules = [
            'ExamConstraintService.php' => [],
            'ExamLifecycleService.php' => ['ExamConstraintService'],
            'ExamScoringService.php' => [],
            'ExamParticipationService.php' => ['ExamScoringService'],
            'OperatorExamService.php' => ['ExamScoringService'],
        ];

        $violations = [];
        $serviceClassNames = [
            'ExamConstraintService',
            'ExamLifecycleService',
            'ExamScoringService',
            'ExamParticipationService',
            'OperatorExamService',
        ];

        foreach ($serviceFiles as $fileName) {
            $path = app_path("Services/{$fileName}");
            $content = File::get($path);
            $selfClass = str_replace('.php', '', $fileName);
            $allowed = $rules[$fileName];

            foreach ($serviceClassNames as $candidate) {
                if ($candidate === $selfClass) {
                    continue;
                }

                if (in_array($candidate, $allowed, true)) {
                    continue;
                }

                if (preg_match('/\b'.$candidate.'\b/', $content) === 1) {
                    $violations[] = "{$fileName} -> {$candidate}";
                }
            }
        }

        $this->assertSame(
            [],
            $violations,
            'Service dependency direction violated: '.implode(', ', $violations)
        );
    }
}
