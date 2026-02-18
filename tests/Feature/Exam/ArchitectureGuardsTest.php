<?php

namespace Tests\Feature\Exam;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ArchitectureGuardsTest extends TestCase
{
    public function test_exam_status_mutation_is_allowed_only_in_lifecycle_service(): void
    {
        $files = array_merge(
            File::allFiles(app_path()),
            File::allFiles(database_path()),
            File::allFiles(base_path('routes'))
        );

        $allowedFiles = [
            str_replace('\\', '/', app_path('Services/ExamLifecycleService.php')),
        ];

        $violations = [];

        foreach ($files as $file) {
            $absolutePath = str_replace('\\', '/', $file->getRealPath());
            if (in_array($absolutePath, $allowedFiles, true)) {
                continue;
            }

            $content = File::get($file->getRealPath());

            $directUpdate = preg_match("/->update\\(\\s*\\[\\s*'status'\\s*=>\\s*Exam::STATUS_[A-Z_]+/s", $content) === 1;
            $directAssign = preg_match('/->status\\s*=\\s*Exam::STATUS_[A-Z_]+/', $content) === 1;
            $directForceFill = preg_match("/->forceFill\\(\\s*\\[\\s*'status'\\s*=>\\s*Exam::STATUS_[A-Z_]+/s", $content) === 1;

            if ($directUpdate || $directAssign || $directForceFill) {
                $violations[] = str_replace('\\', '/', $absolutePath);
            }
        }

        $this->assertSame(
            [],
            $violations,
            'Exam status mutation must go through ExamLifecycleService only. Violations: '.implode(', ', $violations)
        );
    }
}
