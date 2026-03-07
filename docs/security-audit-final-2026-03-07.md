# Security Audit Final - March 7, 2026

Dokumen ini merangkum audit keamanan final untuk sistem ujian sekolah (role model: `ADMIN`, `OPERATOR`, `TEACHER`, `STUDENT`).

## Scope

- Authentication dan role middleware.
- Authorization via Gate/Policy.
- Zero-trust check untuk endpoint lintas role.
- Domain scope peserta (class/school year/target grade).
- High-risk endpoint (publish exam, operator force-submit, student start exam).

## Implemented Controls

1. Role guard middleware terpasang:
- `admin`, `operator`, `teacher`, `student` di [bootstrap/app.php](/c:/laragon/www/website_ujian/bootstrap/app.php).

2. Gate policy terpasang:
- `ExamPolicy`, `ExamAttemptPolicy` didaftarkan di [AppServiceProvider.php](/c:/laragon/www/website_ujian/app/Providers/AppServiceProvider.php).
- Gate granular:
  - `manage-exams`, `manage-users`, `manage-academics`
  - `monitor-exams`, `author-exams`, `view-homeroom-results`, `take-exams`

3. Domain scope peserta:
- Ujian peserta disaring berdasarkan:
  - `class_id` (jika ada),
  - `school_year_id` (jika ada),
  - `target_grade_level` (jika ada).

4. High-risk action hard checks:
- Publish exam melewati `ExamConstraintService`.
- Operator action (`force-submit`, `reopen`, `manual-score`) lewat service + audit.
- Student start/answer/submit melewati policy + service constraint.

## Role-Permission Matrix Test

File:
- [RolePermissionMatrixTest.php](/c:/laragon/www/website_ujian/tests/Feature/Security/RolePermissionMatrixTest.php)

Yang diuji:
- GET core module routes:
  - `admin.exams.index`
  - `admin.subjects.index`
  - `operator.exams.index`
  - `teacher.exams.index`
  - `teacher.homeroom.results.index` (assigned vs unassigned teacher)
  - `student.exams.index`
- POST sensitive routes:
  - `admin.exams.publish`
  - `operator.exams.force-submit`
  - `student.exams.start`

Hasil eksekusi:
- `2 passed (52 assertions)` untuk matrix test.

## Supporting Security Tests (executed)

- [MaliciousAuthorTest.php](/c:/laragon/www/website_ujian/tests/Feature/Exam/MaliciousAuthorTest.php)
- [AttemptAuthorizationTest.php](/c:/laragon/www/website_ujian/tests/Feature/Exam/AttemptAuthorizationTest.php)

Hasil gabungan:
- `11 passed (72 assertions)`.

## Residual Risks

1. Load/security stress belum dijalankan serentak 1000 concurrent user.
2. Belum ada abuse test otomatis untuk brute-force pattern di endpoint ber-throttle.
3. Belum ada explicit CI gate untuk deny-list route exposure (regression route hardening).

## Recommended Next Hardening

1. Tambah load test profile 1000 concurrent (start + timer + answer autosave).
2. Tambah test untuk throttle behavior dan lockout semantics.
3. Tambah CI job wajib untuk:
- `tests/Feature/Security/*`
- `tests/Feature/Exam/MaliciousAuthorTest.php`
- `tests/Feature/Exam/AttemptAuthorizationTest.php`

