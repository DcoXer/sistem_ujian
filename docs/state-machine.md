# State Machine Specification

Dokumen ini adalah sumber kebenaran transisi state domain ujian.

## 1. Exam State

State:
- `draft`
- `running`
- `finished`

Transisi valid:
- `draft -> running`
- `running -> finished`

Transisi tidak valid:
- `draft -> finished` (langsung loncat)
- `running -> draft` (rollback)
- `finished -> running` (reopen exam global)
- `finished -> draft`

Authority:
- Hanya `ExamLifecycleService` yang boleh memutasi `exam.status`.

Trigger:
- Publish exam: `publishDraftExam()`
- Expire by time: `closeExpiredExams()`

## 2. Attempt State

State:
- `active`
- `submitted`
- `finished`

Transisi valid:
- `active -> submitted` (submit manual atau expire)
- `submitted -> finished` (scoring complete)

Transisi tidak valid:
- `submitted -> active` tanpa prosedur service khusus
- `finished -> active` langsung
- `finished -> submitted` rollback

Catatan:
- Jawaban harus dianggap locked setelah submit/expire.
- Setelah `finished`, attempt tidak boleh bisa dijawab ulang.

## 3. UI State Contract (Peserta)

UI state:
- `not_started`
- `running`
- `finished`
- `waiting_result`

UI action:
- `start_enabled`
- `start_disabled`
- `continue_enabled`
- `continue_disabled`
- `waiting_result`
- `result`

Sumber kontrak:
- `app/Support/ExamUiState.php`
- `app/Support/ExamUiAction.php`

Prinsip:
- Frontend hanya render dari state/action.
- Frontend tidak boleh menentukan keputusan domain.

## 4. Conflict Handling

Gunakan `409 Conflict` untuk kasus state tidak valid pada konteks request saat ini, contoh:
- peserta akses hasil sebelum exam `finished`
- timer endpoint menemukan data attempt tidak valid (`started_at` kosong)

`403 Forbidden` dipakai untuk role/policy authorization, bukan state conflict.

## 5. Guardrail Test Mapping

- Lifecycle transitions: `tests/Feature/Exam/LifecycleTest.php`
- Expiration transitions: `tests/Feature/Exam/ExpirationTest.php`
- Result visibility gate: `tests/Feature/Exam/ResultVisibilityTest.php`
- Architecture mutation lock: `tests/Feature/Exam/ArchitectureGuardsTest.php`
