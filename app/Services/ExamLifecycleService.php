<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\Semester;
use App\Models\TeacherSubject;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamLifecycleService
{
    public function __construct(
        protected ExamConstraintService $constraintService
    ) {}

    /**
     * Lifecycle authority service for exam status only.
     * Dependency direction:
     * - must not depend on other domain services.
     * - may be called by controllers/commands as orchestration entrypoint.
     */
    /**
     * Scheduler authority path. Request paths may call this as fallback.
     */
    public function closeExpiredExams(): int
    {
        return Exam::query()
            ->where('status', Exam::STATUS_RUNNING)
            ->where('end_at', '<', now())
            ->update(['status' => Exam::STATUS_FINISHED]);
    }

    /**
     * Bulk-create draft exams from grade-level teacher-subject assignments.
     *
     * @param  array<string, mixed>  $data
     * @return array{created: int, skipped: int}
     */
    public function bulkCreateFromAssignments(array $data, Request $request, SecurityAuditService $auditService): array
    {
        $semester = Semester::query()->with('schoolYear')->findOrFail($data['semester_id']);

        $assignments = TeacherSubject::query()
            ->where('grade_level', $data['target_grade_level'])
            ->whereNull('class_id')
            ->with(['teacher:id,name', 'subject:id,name,code'])
            ->get();

        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($assignments, $data, $semester, $request, $auditService, &$created, &$skipped) {
            foreach ($assignments as $assignment) {
                $title = $data['exam_type'].' '.$semester->name.' - '.$assignment->subject->name
                    .' Kelas '.$data['target_grade_level'];

                $alreadyExists = Exam::query()
                    ->where('semester_id', $data['semester_id'])
                    ->where('exam_type', $data['exam_type'])
                    ->where('subject_id', $assignment->subject_id)
                    ->where('target_grade_level', $data['target_grade_level'])
                    ->exists();

                if ($alreadyExists) {
                    $skipped++;

                    continue;
                }

                $exam = Exam::create([
                    'title' => $title,
                    'semester_id' => $data['semester_id'],
                    'school_year_id' => $semester->school_year_id,
                    'exam_type' => $data['exam_type'],
                    'subject_id' => $assignment->subject_id,
                    'teacher_id' => $assignment->teacher_id,
                    'author_id' => $assignment->teacher_id,
                    'target_grade_level' => $data['target_grade_level'],
                    'class_id' => null,
                    'authoring_start_at' => $data['authoring_start_at'],
                    'authoring_end_at' => $data['authoring_end_at'],
                    'start_at' => $data['start_at'],
                    'end_at' => $data['end_at'],
                    'duration_minutes' => $data['duration_minutes'],
                    'status' => Exam::STATUS_DRAFT,
                    'created_by' => $request->user()->id,
                ]);

                $auditService->log($request, 'exam_created', $exam, [
                    'title' => $exam->title,
                    'bulk' => true,
                ]);

                $created++;
            }
        });

        return ['created' => $created, 'skipped' => $skipped];
    }

    /**
     * Publish exam via single lifecycle choke-point.
     *
     * @throws AuthorizationException
     */
    public function publishDraftExam(Exam $exam): Exam
    {
        return DB::transaction(function () use ($exam) {
            $exam = Exam::query()
                ->with(['questions.options', 'teacher'])
                ->lockForUpdate()
                ->findOrFail($exam->id);

            $this->constraintService->validatePublishability($exam);

            if (! $exam->canTransitionTo(Exam::STATUS_RUNNING)) {
                throw new AuthorizationException('Transisi state ujian tidak valid.');
            }

            $exam->update(['status' => Exam::STATUS_RUNNING]);

            return $exam->fresh();
        });
    }
}
