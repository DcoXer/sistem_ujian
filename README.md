# Exam Lifecycle Engine built on Laravel

Sistem ini menyelesaikan masalah eksekusi ujian berbasis waktu dengan lifecycle ketat, role boundary tegas, dan guardrail test agar state tidak bocor.

## Project Positioning
Ini adalah **Proof of Concept (PoC)** untuk **Enterprise-grade Exam Engine**.

Target utama repository ini adalah menunjukkan arsitektur domain, integritas state machine, dan strategi concurrency. Live demo bisa berjalan pada environment terbatas, tetapi kapasitas uptime production tetap bergantung pada infrastruktur runtime.

## Core Guarantees
- Lifecycle exam dikunci: `draft -> running -> finished`.
- Attempt peserta dikunci: `active -> submitted -> finished`.
- Mutasi `exam.status` hanya lewat `ExamLifecycleService`.
- Admin menetapkan author saat create exam.
- Author yang ditetapkan mengelola pembuatan/edit soal saat exam masih draft.
- Operator tidak bisa publish exam.
- Peserta tidak bisa lihat result sebelum exam `finished`.

Jika salah satu jaminan di atas dilanggar, itu dianggap bug kritis.

## Concurrency & Integrity
- **Optimistic Concurrency Control (OCC)** dipakai pada autosave jawaban peserta untuk mencegah race condition/out-of-order write menimpa jawaban terbaru.
- **Atomic cache lock** dipakai pada exam content caching untuk mencegah cache stampede (dogpile effect) saat request serentak pada cache miss.
- **409 conflict contract** dipakai saat terjadi stale write/state conflict agar frontend bisa recovery terarah, bukan silent overwrite.
- **State mutation authority** tetap di service layer; controller tidak memutuskan domain state.

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

## Graceful Degradation (Demo Environment)
Demo live dapat berjalan dengan fallback konfigurasi terbatas (contoh shared hosting tanpa Redis):
- cache menggunakan store dasar (file/database),
- scheduler menyesuaikan kemampuan hosting,
- beberapa optimasi throughput tidak maksimal.

Kode tetap disiapkan untuk scale-up saat infrastruktur memadai (Redis, worker/scheduler stabil, tuning PHP-FPM/Nginx/DB).

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
- Architecture changelog: `docs/changelog-architecture.md`
- ADR index: `docs/adr/README.md`
- Legacy migration decision: `docs/legacy-migrations.md`
- Freeze line decision: `docs/decisions/0001-exam-lifecycle-is-frozen.md`
