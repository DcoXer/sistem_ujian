# Architecture Changelog

Log keputusan arsitektur penting untuk exam engine.

## 2026-02-15 - ADR Formalization

### Decision
Menjadikan keputusan arsitektur sebagai ADR formal agar governance konsisten dan mudah diaudit.

### Changes
- Tambah index ADR: `docs/adr/README.md`
- Tambah:
  - `ADR-001-exam-status-mutation-via-lifecycle-service-only.md`
  - `ADR-002-domain-centric-exam-test-suite.md`
  - `ADR-003-backend-owned-ui-state-contract.md`

### Consequences
- Keputusan arsitektur tidak lagi implicit di diskusi/commit history.
- Perubahan arah wajib dibuat sebagai ADR baru (bukan edit diam-diam keputusan lama).

## 2026-02-15 - Legacy Migration Policy

### Decision
Menetapkan migration legacy `match/team` sebagai history yang dipertahankan.

### Changes
- Tambah dokumen keputusan: `docs/legacy-migrations.md`

### Consequences
- Tidak ada ambiguity apakah migration legacy adalah artefak sementara atau jejak resmi.
- Perubahan strategi legacy di masa depan wajib ADR baru.

## 2026-02-15 - Exam Engine Hardening

### Decision
Memusatkan mutasi state exam di satu pintu (`ExamLifecycleService`) dan menambah guardrail test berbasis arsitektur.

### Why
- Mencegah mutasi status liar dari controller/command lain.
- Menjaga konsistensi state machine jangka panjang.
- Mengurangi risiko regresi diam-diam saat tim berkembang.

### Changes
- Publish exam dipindah ke `ExamLifecycleService::publishDraftExam()`.
- Finish expired command memanggil `ExamLifecycleService::closeExpiredExams()`.
- Ditambahkan test guard:
  - `tests/Feature/Exam/ArchitectureGuardsTest.php`
  - `tests/Feature/Exam/LifecycleTest.php` (lock `finished` tidak bisa publish ulang)

### Consequences
- Semua fitur baru yang menyentuh `exam.status` wajib lewat lifecycle service.
- PR yang menambah mutasi status di luar service akan gagal oleh test guard.

## 2026-02-15 - Test Suite Domain-Centric

### Decision
Merapikan test ke `tests/Feature/Exam` untuk menegaskan ini exam engine, bukan CRUD generik.

### Why
- Signal arsitektur lebih jelas.
- Guardrail domain lebih mudah diaudit.

### Changes
- Struktur test:
  - `LifecycleTest.php`
  - `ExpirationTest.php`
  - `ResultVisibilityTest.php`
  - `AttemptAuthorizationTest.php`
  - `OperatorControlTest.php`
  - `ArchitectureGuardsTest.php`
- Test noise/non-domain dibersihkan.

### Consequences
- Regression gate fokus pada alur kritis ujian.

## 2026-02-15 - UI Contract Stabilization

### Decision
Mengekstrak state/action UI peserta ke konstanta backend.

### Why
- Hindari hardcoded string liar di frontend.
- Menjaga konsistensi contract frontend-backend.

### Changes
- Tambah:
  - `app/Support/ExamUiState.php`
  - `app/Support/ExamUiAction.php`
- Controller dan Blade peserta menggunakan konstanta tersebut.

### Consequences
- Perubahan contract cukup dilakukan dari satu titik.
