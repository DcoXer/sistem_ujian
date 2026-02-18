# Documentation Entry Point

Ini adalah entry point tunggal dokumentasi untuk **Exam Lifecycle Engine built on Laravel**.

## What Problem This System Solves
- Menjalankan ujian berbasis waktu dengan lifecycle yang konsisten.
- Memisahkan batas wewenang admin/operator/peserta secara tegas.
- Menjaga hasil ujian tetap valid walau ada refresh, reconnect, atau polling realtime.
- Mencegah regresi domain lewat guardrail test.

## What This System Refuses To Do
- Menolak mutasi `exam.status` di luar `ExamLifecycleService`.
- Menolak transisi lifecycle ilegal (contoh: `finished -> running`).
- Menolak peserta melihat hasil sebelum exam `finished`.
- Menolak operator publish/restart exam global.
- Menolak frontend menjadi authority keputusan domain.

## Read In This Order
1. `architecture.md`
2. `state-machine.md`
3. `api.md`
4. `testing.md`
5. `deployment.md`

## Operational Docs
- `runbook.md`
- `quickstart-role-playbook.md`
- `sequence.md`

## Governance
- ADR index: `adr/README.md`
- Architecture changelog: `changelog-architecture.md`
- Legacy migration decision: `legacy-migrations.md`
- Freeze line decision: `decisions/0001-exam-lifecycle-is-frozen.md`
