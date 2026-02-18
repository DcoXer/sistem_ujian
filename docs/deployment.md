# Deployment Checklist

## php.id (Shared Hosting)
- Ikuti panduan lengkap di: `docs/deployment-phpid.md`
- Jika document root tidak bisa diarahkan ke `public`, pakai template:
  - `deploy/shared-hosting/public_html/index.php`
  - `deploy/shared-hosting/public_html/.htaccess`

## Render (GitHub Deploy)
1. Push repo yang sudah berisi `Dockerfile` + `render.yaml`.
2. Di Render dashboard: `New +` -> `Blueprint` -> pilih repo ini.
3. Render akan baca `render.yaml` dan membuat web service otomatis.
4. Isi env wajib saat diminta:
- `APP_KEY` (wajib, format `base64:...`)
- `DATABASE_URL` (Supabase PostgreSQL URL)
- `APP_URL` (URL Render app kamu, contoh `https://sistem-ujian.onrender.com`)
5. Deploy.
6. Setelah service up, jalankan migration sekali dari Render `Shell`:
- `php artisan migrate --force`

Catatan:
- Scheduler lifecycle sudah jalan dari proses app (`php artisan schedule:work`) di entrypoint Docker.
- Jika pakai domain custom, update `APP_URL` ke domain final.

## Fly.io (GitHub Deploy)
1. Pastikan `fly.toml` sudah sesuai:
- `app` diset ke nama app Fly.io milikmu.
- `APP_URL` diset ke domain Fly app milikmu.
2. Mode paling simple (disarankan):
- Isi secret `APP_KEY`
- Isi secret `DATABASE_URL` (langsung paste connection string PostgreSQL Supabase/Fly Postgres)
- Isi `DB_CONNECTION=pgsql`
- Isi `DB_SSLMODE=require`
3. Jika deploy lewat website Fly (tanpa terminal), cukup isi 4 nilai di atas lalu `Launch`.
4. Deploy via CLI (opsional):
- `fly deploy`

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
