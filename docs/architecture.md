# Architecture - Exam Engine

## Core Principle
Route menentukan siapa yang boleh masuk, policy menentukan siapa boleh melakukan aksi, service menentukan kapan aksi valid.

Controller hanya orchestration, bukan tempat keputusan domain.

## State Machine

### Exam
- `draft -> running -> finished`
- `finished` tidak boleh kembali ke `running`

### Attempt
- `active -> submitted -> finished`
- attempt tidak boleh diedit setelah lock/submit

## Single Choke Point
Mutasi `exam.status` di luar service ini dianggap bug kritis dan harus ditolak.

Satu-satunya jalur yang diizinkan:
- `app/Services/ExamLifecycleService.php`

Saat ini service ini menangani:
- publish draft exam (`draft -> running`)
- close expired running exam (`running -> finished`)

## Scheduler Authority
Authority operasional lifecycle ada di scheduler:
- `exams:finish-expired`
- `exams:expire-attempts`

Request path hanya fallback sinkronisasi. Request path tidak boleh menjadi sumber keputusan lifecycle utama.

## Role Boundary
- Admin mengubah state exam.
- Author mengelola soal exam draft.
- Operator tidak publish exam dan tidak override hasil objektif.
- Peserta hanya mengelola attempt milik sendiri.

Author adalah editor, bukan owner. Ownership soal terikat ke exam melalui `exam_questions.exam_id`.

## Frontend Contract
UI action/state tidak hardcoded bebas:
- `app/Support/ExamUiAction.php`
- `app/Support/ExamUiState.php`

Frontend membaca contract ini dari layout data attribute.

Catatan penting:
- `server_now_ms` di frontend hanya seed sinkronisasi waktu, bukan authority domain.

## Service Dependency Direction
- `ExamLifecycleService` dan `ExamScoringService` adalah service primitive (tidak boleh depend ke service lain).
- `ExamParticipationService` dan `OperatorExamService` adalah service orchestration (hanya boleh depend ke primitive scoring).
- Ketergantungan silang antar orchestration service dianggap pelanggaran arsitektur.

## What This Architecture Explicitly Forbids
- Controller mengubah `exam.status` secara langsung.
- Command/job/repository mengubah `exam.status` di luar `ExamLifecycleService`.
- Frontend menentukan lifecycle state sebagai keputusan domain.
- Operator mem-publish atau me-reset exam global.
- Service orchestration saling memanggil tanpa aturan arah dependensi.
