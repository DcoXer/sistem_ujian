# ADR-003: Backend-Owned UI State Contract

- Status: Accepted
- Date: 2026-02-15

## Context
String state/action yang tersebar di frontend rentan divergensi dari domain rule backend.

## Decision
Contract UI state/action dimiliki backend melalui konstanta, frontend hanya membaca dan merender.

## Consequences
- State/action UI menjadi kontrak eksplisit dan terpusat.
- Perubahan rule dilakukan dari backend contract, bukan hardcode frontend.

## Implementation References
- `app/Support/ExamUiState.php`
- `app/Support/ExamUiAction.php`
- `app/Http/Controllers/Peserta/PesertaExamController.php`
- `resources/views/layouts/app.blade.php`
- `resources/js/layout.js`

## Explicitly Forbidden
- Frontend menentukan lifecycle domain (`not_started/running/finished`) sebagai authority.
- Menambah string action baru di frontend tanpa menambah contract backend.
