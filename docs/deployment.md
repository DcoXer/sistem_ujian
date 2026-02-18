# Deployment Checklist

## 1. Build & Config
- Set `.env` production
- `php artisan config:cache`
- `php artisan route:cache`
- `php artisan view:cache`
- `npm run build`

## 2. Database
- `php artisan migrate --force`
- (opsional pertama kali) `php artisan db:seed --force`
- `php artisan storage:link`

## 3. Scheduler (Wajib)
Pastikan scheduler aktif:
- `php artisan schedule:work`
atau cron:
- `* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1`

Tanpa scheduler, lifecycle exam/attempt bisa terlambat sinkron.
Tanpa scheduler, exam bisa tampak berjalan normal tapi melanggar lifecycle.

## 4. Smoke Test
- Login admin berhasil
- Publish exam berhasil
- Peserta bisa start exam saat window waktu valid
- Result peserta tidak bisa diakses sebelum exam `finished`
- Operator tidak bisa publish exam

## 5. Regression Gate
Sebelum release:
- `php artisan test tests/Feature/Exam`

Jika suite exam gagal, release harus ditahan.
