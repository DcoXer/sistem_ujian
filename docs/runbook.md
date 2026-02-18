# Operator Runbook (Incident Handling)

Runbook ini untuk tindakan operasional saat ujian berjalan. Operator fokus ke stabilitas teknis, bukan keputusan akademik final.

## Scope Operator

Boleh:
- monitoring ujian aktif
- force submit attempt pada kasus teknis
- reopen attempt dengan alasan
- mark technical issue (audit trail)
- manual scoring terbatas sesuai policy/intention

Tidak boleh:
- publish exam
- mengubah state exam global
- override hasil objektif tanpa jalur resmi

## Pre-Check Sebelum Tindakan

1. Pastikan exam masih relevan dengan incident.
2. Verifikasi identitas peserta/attempt.
3. Catat alasan teknis singkat dan jelas.
4. Gunakan endpoint resmi, jangan DB update manual.

## Incident Playbook

### A. Peserta disconnect saat waktu ujian berjalan
Langkah:
1. Cek attempt status di monitoring operator.
2. Jika waktu habis atau peserta tidak bisa kembali, lakukan force submit.
3. Isi alasan yang spesifik.

Endpoint:
- `POST /operator/exam-attempts/{attempt}/force-submit`

Expected:
- attempt pindah ke status selesai proses submit/scoring sesuai service.
- audit trail tercatat.

### B. Peserta perlu reopen karena kendala teknis valid
Langkah:
1. Verifikasi exam masih pada state yang mengizinkan reopen (sesuai service rules).
2. Pastikan alasan incident valid.
3. Jalankan reopen via endpoint resmi.

Endpoint:
- `POST /operator/exam-attempts/{attempt}/reopen`

Expected:
- jika state tidak valid, sistem menolak dan tampil error.
- jika valid, audit trail tercatat.

### C. Manual score (kasus terbatas)
Langkah:
1. Pastikan attempt lolos policy `manualScore`.
2. Wajib kirim `intent=manual_essay_scoring`.
3. Beri reason yang jelas.

Endpoint:
- `POST /operator/exam-attempts/{attempt}/manual-score`

Expected:
- sistem menolak jika intent salah atau melanggar rules.
- perubahan terekam via audit.

## Error Handling Cepat

- `403`: operator tidak punya hak untuk aksi itu.
- `409`: state sudah tidak cocok untuk aksi.
- `422`: payload invalid (reason/intent/score).

## Audit Discipline

Setiap aksi operator yang mengubah attempt wajib punya:
- actor (`operator`)
- reason
- timestamp
- action type

Jika salah satu kosong, treat sebagai incident proses.

## Escalation ke Admin

Escalate jika:
- ada indikasi perlu perubahan state exam global
- perlu keputusan kebijakan nilai
- ditemukan pola abuse yang berulang

Operator tidak mengambil keputusan final untuk domain yang di luar scope teknis.
