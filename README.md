# Sistem Ujian Online — MI/SD

Sistem ujian online berbasis web untuk sekolah Madrasah Ibtidaiyah (MI) / Sekolah Dasar (SD), dibangun di atas Laravel 12 dengan Livewire dan Tailwind CSS v4.

## Gambaran Sistem

Sistem ini menangani seluruh lifecycle ujian — dari pembuatan soal oleh guru, distribusi ke siswa, pengerjaan real-time, hingga rekap nilai per wali kelas — dengan role boundary tegas dan state machine yang dikunci.

### Role yang Tersedia

| Role | Tanggung Jawab |
|---|---|
| **Admin** | Kelola semester, rombel, assignment guru, buat ujian massal per tingkat |
| **Guru** | Buat/edit soal dalam authoring window yang ditentukan admin |
| **Wali Kelas** | Lihat nilai siswa di rombel yang diampu |
| **Siswa** | Kerjakan ujian, lihat nilai |
| **Operator** | Pengawasan operasional ujian |

## Fitur Utama

### Manajemen Semester & Ujian
- Semester terikat ke tahun ajaran (`school_years`), satu semester aktif dalam satu waktu
- Jenis ujian: **UH** (Ulangan Harian), **UTS** (Ujian Tengah Semester), **UAS** (Ujian Akhir Semester), **PAT** (Penilaian Akhir Tahun)
- Admin bisa membuat ujian sekaligus untuk semua rombel dalam satu tingkat kelas (*bulk create*)

### Assignment Guru Per Tingkat Kelas
- Satu guru mapel bisa di-assign ke **tingkat kelas** (grade level), bukan hanya satu rombel
- Assignment per tingkat otomatis mencakup semua rombel di tingkat tersebut
- Mendukung dua mode assignment: per tingkat kelas atau per rombel spesifik

### Lifecycle Ujian
- State dikunci: `draft → running → finished`
- Authoring window: guru hanya bisa buat/edit soal dalam rentang waktu yang ditentukan admin
- Setelah authoring window tutup, soal terkunci otomatis

### Pengerjaan Ujian Real-time
- Autosave jawaban setiap perubahan (tanpa harus tekan tombol simpan)
- Timer sisa waktu ter-sync dari server
- Progress bar per nomor soal
- Navigasi soal bebas (bisa lompat maju/mundur)

### Integritas Concurrency
- **Optimistic Concurrency Control (OCC)** pada autosave jawaban mencegah race condition/out-of-order write
- **Atomic cache lock** pada exam content caching mencegah cache stampede
- **409 conflict contract** saat terjadi stale write agar frontend bisa recovery terarah

## Quick Start

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

## Scheduled Commands

```bash
php artisan exams:finish-expired   # Finalize exam yang melewati end_at
php artisan exams:expire-attempts  # Expire attempt siswa yang overtime
```

Scheduler wajib aktif agar lifecycle berjalan konsisten.

## Stack Teknologi

- **Backend:** Laravel 12, PHP 8.2
- **Frontend:** Livewire v3, Tailwind CSS v4, Alpine.js
- **Database:** MySQL (production), SQLite (testing)
- **Testing:** PHPUnit 11

## Regression Gate

```bash
php artisan test                        # Semua test (65 tests, 214 assertions)
php artisan test tests/Feature/Exam     # Exam engine saja
```

## Core Guarantees

- Lifecycle exam dikunci: `draft → running → finished`
- Attempt siswa dikunci: `active → submitted → finished`
- Mutasi `exam.status` hanya lewat `ExamLifecycleService`
- Guru hanya bisa kelola soal exam `draft` yang di-assign admin, dalam authoring window
- Siswa tidak bisa lihat nilai sebelum exam `finished`
- Operator tidak bisa publish exam

Pelanggaran terhadap jaminan di atas dianggap bug kritis.

## Dokumentasi Teknis

- Arsitektur: `docs/architecture.md`
- State machine: `docs/state-machine.md`
- API contract: `docs/api.md`
- Sequence flow: `docs/sequence.md`
- Role quickstart: `docs/quickstart-role-playbook.md`
- Testing strategy: `docs/testing.md`
- Operator runbook: `docs/runbook.md`
- Deploy checklist: `docs/deployment.md`
