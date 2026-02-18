# Architecture Decisions (Practical Notes)

Dokumen ini bukan spesifikasi teknis detail, tapi alasan inti kenapa boundary sistem dibuat seperti sekarang.

## 1) Kenapa Admin Tidak Bikin Soal

Admin fokus pada governance ujian: membuat exam, menentukan window, assign author, publish, dan audit hasil.

Jika admin juga menulis soal, boundary ownership kabur:
- conflict of interest meningkat,
- review dan audit lebih sulit,
- operasi harian jadi bottleneck di satu role.

Karena itu, pembuatan/edit soal dipisah ke role `author`.

## 2) Kenapa Author Tidak Bisa Publish

Author bertanggung jawab pada konten soal, bukan lifecycle release.

Publish adalah keputusan operasional dan governance yang berdampak ke peserta secara global.
Keputusan ini harus tetap di role `admin` agar ada pemisahan:
- content ownership (author),
- release authority (admin).

Pemisahan ini mencegah publish prematur dan mengurangi risiko abuse internal.

## 3) Kenapa `question` Tidak Punya `author_id`

`Question` adalah milik `Exam`, bukan milik permanen user tertentu.

`author` diperlakukan sebagai editor yang ditugaskan ke exam.
Alasan:
- ownership domain tetap bersih: `question -> exam`,
- rotasi author tetap memungkinkan tanpa merusak model data,
- histori perubahan dicatat lewat audit, bukan lewat coupling struktur tabel.

Prinsip: author adalah actor, exam adalah owner.

## 4) Kenapa Replay Conflict Jadi HTTP 409

Request replay pada state yang sudah tidak valid bukan masalah permission (`403`), tapi konflik state resource.

Contoh:
- submit attempt yang sudah submitted/finished,
- reopen attempt aktif,
- manual score pada attempt yang sudah scored.

Status `409 Conflict` dipakai agar:
- semantik HTTP tepat,
- security testing (ZAP/repeater) tidak memberikan false-positive `200`,
- client bisa membedakan auth error vs state conflict.

## 5) Kenapa Audit Mandatory

Sistem ujian tanpa audit tidak bisa dibuktikan integritasnya.

Audit wajib karena:
- tindakan sensitif (publish, reopen, manual score, delete question) harus dapat ditelusuri,
- diperlukan untuk investigasi insiden dan sengketa hasil,
- menjadi guardrail terhadap insider abuse.

Minimal audit menyimpan:
- siapa (actor),
- kapan (timestamp),
- apa (action),
- dari mana (IP, user-agent),
- konteks (meta/reason).

Tanpa audit, sistem bisa berjalan, tapi tidak bisa dipertanggungjawabkan.

