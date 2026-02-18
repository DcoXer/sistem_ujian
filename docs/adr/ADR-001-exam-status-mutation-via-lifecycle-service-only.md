# ADR-001: Exam Status Mutation via Lifecycle Service Only

- Status: Accepted
- Date: 2026-02-15

## Context
Status `exam` adalah inti integritas lifecycle ujian. Mutasi tersebar di controller/command membuat state drift dan regresi sulit dilacak.

## Decision
Semua mutasi `exam.status` hanya boleh terjadi di `ExamLifecycleService`.

Mutasi di luar service ini dianggap bug kritis dan harus ditolak.

## Consequences
- Publish exam wajib lewat `publishDraftExam()`.
- Penutupan exam by time wajib lewat `closeExpiredExams()`.
- Controller/command/job tidak boleh update `exam.status` langsung.

## Implementation References
- `app/Services/ExamLifecycleService.php`
- `app/Http/Controllers/Admin/AdminExamController.php`
- `app/Console/Commands/FinishExpiredExams.php`

## Guardrails
- `tests/Feature/Exam/ArchitectureGuardsTest.php`
- `tests/Feature/Exam/LifecycleTest.php`

## Explicitly Forbidden
- `->update(['status' => Exam::STATUS_*])` di luar `ExamLifecycleService`.
- rollback state `finished -> running`.
