# Exam Lifecycle Engine built on Laravel

Sistem ini menyelesaikan masalah eksekusi ujian berbasis waktu dengan lifecycle ketat, role boundary tegas, dan guardrail test agar state tidak bocor.

## Core Guarantees
- Lifecycle exam dikunci: `draft -> running -> finished`.
- Attempt peserta dikunci: `active -> submitted -> finished`.
- Mutasi `exam.status` hanya lewat `ExamLifecycleService`.
- Admin menetapkan author saat create exam.
- Author yang ditetapkan mengelola pembuatan/edit soal saat exam masih draft.
- Operator tidak bisa publish exam.
- Peserta tidak bisa lihat result sebelum exam `finished`.

Jika salah satu jaminan di atas dilanggar, itu dianggap bug kritis.

## Author Role: Explicit Limitations
- Author tidak bisa create exam.
- Author tidak bisa publish exam.
- Author tidak bisa mengubah metadata exam (`title`, jadwal, durasi, status).
- Author hanya bisa mengelola soal untuk exam `draft` yang di-assign admin.
- Author tidak punya ownership atas exam; author hanya editor.
- Ownership soal tetap pada exam (`exam_questions.exam_id`), bukan pada user author.

## Quick Start
1. `composer install`
2. `npm install`
3. `cp .env.example .env`
4. `php artisan key:generate`
5. `php artisan migrate --seed`
6. `php artisan storage:link`
7. `npm run build` (atau `npm run dev`)
8. `php artisan serve`

## Runtime Commands
- `php artisan exams:finish-expired`
- `php artisan exams:expire-attempts`

Scheduler wajib aktif agar lifecycle berjalan konsisten.

## Regression Gate
- Jalankan semua: `php artisan test`
- Jalankan exam engine saja: `php artisan test tests/Feature/Exam`

## Documentation
- Documentation entry point: `docs/README.md`
- Arsitektur: `docs/architecture.md`
- API contract: `docs/api.md`
- State machine: `docs/state-machine.md`
- Sequence flow: `docs/sequence.md`
- Role quickstart: `docs/quickstart-role-playbook.md`
- Testing strategy: `docs/testing.md`
- Operator runbook: `docs/runbook.md`
- Deploy checklist: `docs/deployment.md`
- Shared hosting (php.id): `docs/deployment-phpid.md`
- Architecture changelog: `docs/changelog-architecture.md`
- ADR index: `docs/adr/README.md`
- Legacy migration decision: `docs/legacy-migrations.md`
- Freeze line decision: `docs/decisions/0001-exam-lifecycle-is-frozen.md`
