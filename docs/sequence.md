# End-to-End Sequence

Dokumen ini menjelaskan urutan sistem ujian dari awal sampai hasil tampil.

## 1. Admin Menyiapkan Ujian

1. Admin login.
2. Admin membuat exam (`status = draft`) dan memilih author penanggung jawab soal.
3. Author yang ditetapkan admin menambah dan mengedit soal.
4. Admin publish exam (`draft -> running`) lewat `ExamLifecycleService`.

Catatan:
- exam tanpa soal tidak bisa publish.
- exam `finished` tidak bisa dipublish ulang.
- admin tidak mengelola soal secara langsung.

## 2. Peserta Memulai Attempt

1. Peserta login.
2. Peserta membuka daftar exam.
3. Peserta klik start pada exam yang valid window waktunya.
4. `ExamParticipationService::startExam()` membuat attempt `active` jika belum ada.

Catatan:
- jika peserta sudah submit/finished sebelumnya, start ditolak.

## 3. Peserta Menjawab

1. Peserta membuka halaman attempt.
2. Jawaban disimpan via endpoint answer (autosave).
3. Policy + service memvalidasi:
   - ownership peserta
   - attempt masih `active`
   - exam masih `running`
   - belum melewati deadline

## 4. Submit dan Lock

1. Peserta menekan submit.
2. Service mengubah attempt:
   - `active -> submitted`
   - set `submitted_at`
   - lock jawaban (`answers_locked_at`)
3. Scoring service memproses nilai.
4. Attempt diproses menuju status akhir (`finished`).

## 5. Lifecycle Otomatis (Scheduler)

Setiap menit:
- `exams:finish-expired` menutup exam `running` yang lewat `end_at`.
- `exams:expire-attempts` menutup attempt aktif yang melewati batas waktu.

Ini adalah authority operasional. Request path hanya fallback sinkronisasi.

## 6. Hasil Ditampilkan

1. Peserta mengakses result.
2. Jika exam belum `finished`, sistem mengembalikan `409`.
3. Setelah exam `finished`, peserta bisa melihat result miliknya.
4. Admin/operator bisa melihat monitoring/result sesuai policy.

## 7. Operator Incident Flow (Ringkas)

Operator dapat:
- force submit attempt teknis
- reopen attempt dengan reason
- mark issue
- manual score terbatas (dengan intent valid)

Operator tidak dapat publish exam.
