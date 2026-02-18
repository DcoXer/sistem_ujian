# ADR-002: Domain-Centric Exam Test Suite

- Status: Accepted
- Date: 2026-02-15

## Context
Test bawaan framework/noise tidak memberi perlindungan terhadap kegagalan domain ujian.

## Decision
Test guardrail dipusatkan ke suite `tests/Feature/Exam` dan diperlakukan sebagai regression gate wajib sebelum release.

## Consequences
- Fokus test pada lifecycle, expiration, authorization, result visibility, operator boundary, dan architecture guard.
- Test kosmetik/non-domain boleh dihapus.

## Implementation References
- `tests/Feature/Exam/LifecycleTest.php`
- `tests/Feature/Exam/ExpirationTest.php`
- `tests/Feature/Exam/ResultVisibilityTest.php`
- `tests/Feature/Exam/AttemptAuthorizationTest.php`
- `tests/Feature/Exam/OperatorControlTest.php`
- `tests/Feature/Exam/ArchitectureGuardsTest.php`

## Explicitly Forbidden
- Menghapus guardrail test domain kritis tanpa pengganti setara.
- Meluluskan deploy saat suite exam gagal.
