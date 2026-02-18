# Quickstart Role Playbook

Panduan cepat operasional harian per role.

## Admin Playbook

### Tujuan
Menyiapkan dan mengontrol lifecycle ujian.

### Checklist Harian
1. Login sebagai admin.
2. Buat exam baru (`draft`) jika diperlukan.
3. Tentukan author yang bertugas membuat soal exam.
4. Verifikasi jadwal (`start_at`, `end_at`, `duration_minutes`).
5. Setelah author menyelesaikan soal, publish exam.
6. Pantau dashboard dan data hasil.

### Batasan
- Tidak mengubah exam yang sudah `running/finished` tanpa prosedur resmi.
- Tidak bypass lifecycle via DB manual update.

## Operator Playbook

### Tujuan
Menjaga stabilitas teknis ujian berjalan.

### Checklist Harian
1. Login sebagai operator.
2. Buka monitoring exam aktif.
3. Pantau peserta:
   - status attempt
   - sisa waktu
   - indikasi kendala teknis
4. Jika perlu:
   - force submit (dengan reason)
   - reopen attempt (dengan reason)
   - mark issue untuk audit
5. Manual score hanya bila memang kasus valid dan intent sesuai.

### Batasan
- Operator tidak boleh publish exam.
- Operator tidak boleh override hasil objektif sembarangan.

## Author Playbook

### Tujuan
Menyusun dan mengedit soal untuk exam yang dibuat admin.

### Checklist Harian
1. Login sebagai author.
2. Buka panel `Kelola Soal Ujian`.
3. Pilih exam berstatus `draft`.
4. Tambah/edit soal beserta opsi dan jawaban benar.
5. Pastikan jumlah soal sudah sesuai kebutuhan sebelum memberi konfirmasi ke admin.

### Batasan
- Author tidak boleh publish exam.
- Author tidak boleh mengubah exam yang bukan `draft`.

## Peserta Playbook

### Tujuan
Mengerjakan ujian secara sah satu kali sesuai aturan.

### Checklist
1. Login sebagai peserta.
2. Buka daftar ujian.
3. Start exam saat tombol tersedia.
4. Jawab soal dan pastikan tersimpan.
5. Submit saat selesai.
6. Lihat hasil saat exam sudah `finished`.

### Batasan
- Tidak bisa edit jawaban setelah submit/lock.
- Tidak bisa akses result sebelum exam selesai global.

## Escalation Matrix

- Masalah teknis attempt peserta -> Operator.
- Masalah state exam global / publish / policy nilai -> Admin.
- Bug arsitektur/state machine -> Tim dev (wajib refer ke docs test + architecture).

## Referensi Cepat
- Arsitektur: `docs/architecture.md`
- API contract: `docs/api.md`
- State machine: `docs/state-machine.md`
- Runbook operator: `docs/runbook.md`
