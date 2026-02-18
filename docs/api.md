# API Contract (Web Routes)

Dokumen ini adalah kontrak endpoint internal untuk exam engine. Semua route ada di `routes/web.php` dan menggunakan session auth (bukan token API publik).

## Aturan Umum
- Auth wajib: semua route domain exam berada di belakang middleware `auth`.
- Role gate di route: `admin`, `author`, `operator`, `peserta`.
- Policy gate di action: `ExamPolicy`, `ExamAttemptPolicy`.
- State decision di service:
  - `ExamLifecycleService`
  - `ExamParticipationService`
  - `OperatorExamService`

## Status Code Semantik
- `200`/`302`: sukses (web flow normal redirect/view).
- `403`: role/policy tidak berhak.
- `404`: resource tidak ditemukan / tidak terkait.
- `409`: conflict state (mis. hasil belum boleh diakses, started_at invalid).
- `422`: validasi input gagal.

## Admin Endpoints
Prefix: `/admin`  
Middleware: `auth`, `admin`

- `GET /admin/dashboard/realtime` (`admin.dashboard.realtime`)
  - Tujuan: realtime panel admin.

- `GET /admin/exams` (`admin.exams.index`)
  - Tujuan: list ujian + statistik ringkas.

- `POST /admin/exams` (`admin.exams.store`)
  - Tujuan: create exam (status awal `draft`).
  - Validasi: `title`, `start_at`, `end_at`, `duration_minutes`, `author_id` (role `author`).

- `GET /admin/exams/{exam}` (`admin.exams.show`)
  - Tujuan: detail exam + soal + attempt.

- `POST /admin/exams/{exam}/publish` (`admin.exams.publish`)
  - Tujuan: publish exam (`draft -> running`).
  - Constraint: mutasi status dilakukan via `ExamLifecycleService::publishDraftExam`.

## Author Endpoints
Prefix: `/author`  
Middleware: `auth`, `author`

- `GET /author/exams` (`author.exams.index`)
  - Tujuan: list exam untuk authoring soal.

- `GET /author/exams/{exam}` (`author.exams.show`)
  - Tujuan: detail exam + authoring view.

- `POST /author/exams/{exam}/questions` (`author.exams.questions.store`)
  - Tujuan: tambah soal untuk exam `draft`.
  - Gate: `ExamPolicy::update` (author + draft only).

- `PUT /author/exams/{exam}/questions/{question}` (`author.exams.questions.update`)
  - Tujuan: edit soal untuk exam `draft`.
  - Gate: `ExamPolicy::update` (author + draft only).

## Operator Endpoints
Prefix: `/operator`  
Middleware: `auth`, `operator`

- `GET /operator/exams` (`operator.exams.index`)
  - Tujuan: monitoring ujian running/upcoming.

- `GET /operator/exams/{exam}` (`operator.exams.show`)
  - Tujuan: monitoring peserta, sisa waktu, status online.

- `POST /operator/exam-attempts/{attempt}/manual-score` (`operator.exams.manual-score`)
  - Middleware tambahan:
    - `can:manualScore,attempt`
    - `manual_score_intent` (`intent` wajib `manual_essay_scoring`)
  - Tujuan: input nilai manual terbatas.

- `POST /operator/exam-attempts/{attempt}/force-submit` (`operator.exams.force-submit`)
  - Tujuan: force submit attempt aktif karena kasus teknis.

- `POST /operator/exam-attempts/{attempt}/reopen` (`operator.exams.reopen`)
  - Tujuan: reopen attempt by reason (tetap taat state).

- `POST /operator/exam-attempts/{attempt}/mark-issue` (`operator.exams.mark-issue`)
  - Tujuan: catat kendala teknis (audit trail).

## Peserta Endpoints
Prefix: `/peserta`  
Middleware: `auth`, `peserta`

- `GET /peserta/exams` (`peserta.exams.index`)
  - Tujuan: daftar ujian untuk peserta + status UI action.

- `GET /peserta/exams/realtime-state` (`peserta.exams.realtime-state`)
  - Tujuan: source realtime state UI.
  - Response inti: `server_now_ms`, `exams[].state`, `exams[].action`, `exams[].attempt_status`.

- `POST /peserta/exams/{exam}/start` (`peserta.exams.start`)
  - Tujuan: mulai ujian (buat/lanjut attempt aktif).
  - Gate: `ExamPolicy::start` + service checks.

- `GET /peserta/exam-attempts/{attempt}` (`peserta.exams.show`)
  - Tujuan: halaman pengerjaan attempt.

- `POST /peserta/exam-attempts/{attempt}/answer` (`peserta.exams.answer`)
  - Tujuan: autosave jawaban.
  - Gate: `ExamAttemptPolicy::answer`.

- `GET /peserta/exam-attempts/{attempt}/timer` (`peserta.exams.timer`)
  - Tujuan: sisa waktu authoritative per attempt.
  - Catatan: jika `started_at` invalid -> `409`.

- `POST /peserta/exam-attempts/{attempt}/submit` (`peserta.exams.submit`)
  - Tujuan: submit final, lock jawaban, trigger scoring.

- `GET /peserta/exam-attempts/{attempt}/result` (`peserta.exams.result`)
  - Tujuan: lihat hasil.
  - Constraint keras: untuk peserta, jika exam belum `finished` -> `409`.

## Profile Endpoints (Semua Auth User)
- `GET /profile` (`profile.edit`)
- `PATCH /profile` (`profile.update`)
- `DELETE /profile` (`profile.destroy`)

## Boundary Rules (Harus Konsisten)
- Admin mengubah state exam.
- Author mengelola soal exam draft.
- Operator mengelola attempt, bukan publish exam.
- Peserta hanya attempt miliknya sendiri.
- Frontend boleh mengantisipasi; backend tetap memutuskan.
