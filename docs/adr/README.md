# ADR Index

Daftar Architecture Decision Record (ADR) resmi untuk exam engine.

- [ADR-001 - Exam Status Mutation via Lifecycle Service Only](ADR-001-exam-status-mutation-via-lifecycle-service-only.md)
- [ADR-002 - Domain-Centric Exam Test Suite](ADR-002-domain-centric-exam-test-suite.md)
- [ADR-003 - Backend-Owned UI State Contract](ADR-003-backend-owned-ui-state-contract.md)

## Aturan ADR
- ADR baru harus immutable; jika ada perubahan arah, buat ADR baru yang menggantikan.
- Referensi implementasi wajib menyertakan file/test guard terkait.
- Jika keputusan menyentuh state machine, sertakan aturan `forbidden`.
