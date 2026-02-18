# 0001 - Exam Lifecycle Is Frozen

- Status: Accepted
- Effective date: 2026-02-15
- Owners: Product Owner, Tech Lead

## Decision
Mulai dokumen ini disahkan, domain lifecycle ujian dianggap **frozen**.

Perubahan domain setelah titik ini **wajib** melalui RFC/ADR sebelum implementasi.

## Final State Machine

### Exam
- `draft -> running -> finished`

Forbidden:
- `finished -> running`
- `running -> draft`
- `draft -> finished` (skip step)

### Attempt
- `active -> submitted -> finished`

Forbidden:
- `submitted -> active` tanpa prosedur resmi yang disetujui
- `finished -> active`
- edit jawaban setelah submit/lock

## Final Invariants
- exam `finished` tidak boleh menyisakan attempt `active` setelah lifecycle sync.
- attempt `submitted` tidak boleh dijawab ulang atau disubmit ulang.
- result peserta tidak boleh terlihat sebelum exam `finished`.
- mutasi `exam.status` hanya boleh terjadi di `ExamLifecycleService`.
- operator tidak boleh publish/reset exam global.

## Authority Boundary
- Admin: mengubah state exam melalui jalur resmi lifecycle.
- Author: membuat dan mengedit soal pada exam `draft`.
- Operator: mengelola attempt teknis dalam batas service/policy.
- Peserta: mengelola attempt milik sendiri sesuai window waktu.
- Frontend: hanya merender state/action, bukan memutuskan domain.

## Change Control (Mandatory)
Perubahan pada salah satu item berikut **harus** lewat RFC/ADR:
- state machine
- invariants
- role authority boundary
- service dependency direction

Minimal syarat perubahan:
1. ADR/RFC baru disetujui.
2. Guardrail test ditambah/diupdate.
3. Dokumen architecture/testing/state-machine diperbarui.

## Enforcement References
- `tests/Feature/Exam/ArchitectureGuardsTest.php`
- `tests/Feature/Exam/ThinControllersGuardTest.php`
- `tests/Feature/Exam/SystemInvariantsTest.php`
- `tests/Feature/Exam/ServiceDependencyDirectionGuardTest.php`
