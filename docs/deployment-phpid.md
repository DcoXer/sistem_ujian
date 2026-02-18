# Deployment Guide: php.id (Shared Hosting)

Panduan ini untuk deploy Laravel ke hosting shared (cPanel-like) tanpa Docker.

## Prasyarat
- Paket hosting php.id sudah aktif.
- PHP minimal 8.2 di hosting.
- MySQL database + user sudah dibuat di panel php.id.
- Kamu sudah punya file source terbaru dari repo ini.

## Ringkasan Alur
1. Build asset di lokal.
2. Upload source Laravel ke hosting.
3. Atur web root ke folder `public` (opsi terbaik).
4. Set `.env` production.
5. Jalankan migrate + cache.
6. Set cron scheduler.

## 1) Build Asset di Lokal (WAJIB)
Jalankan di komputer lokal:

```bash
npm ci
npm run build
composer install --no-dev --optimize-autoloader
```

Tujuan: shared hosting biasanya tidak menyediakan Node build.

## 2) Upload File
- Upload project Laravel ke folder non-public, contoh: `/home/<user>/sistem_ujian`.
- Jangan taruh seluruh source langsung di `public_html` kalau bisa dihindari.

## 3) Atur Document Root
### Opsi A (Disarankan)
Set domain/subdomain document root ke:

`/home/<user>/sistem_ujian/public`

Kalau opsi ini tersedia di panel, pakai ini.

### Opsi B (Jika panel tidak bisa ganti doc root)
Gunakan template di:
- `deploy/shared-hosting/public_html/index.php`
- `deploy/shared-hosting/public_html/.htaccess`

Lalu:
1. Copy dua file itu ke `public_html`.
2. Edit `index.php`, ganti placeholder:
`__APP_BASE_PATH__`
menjadi absolute path app Laravel kamu, contoh:
`/home/username/sistem_ujian`

## 4) Konfigurasi `.env` (Production)
Minimal:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domainkamu
APP_KEY=base64:...

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nama_db
DB_USERNAME=user_db
DB_PASSWORD=password_db

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict
SESSION_LIFETIME=30
```

## 5) Jalankan Artisan di Hosting
Lewat SSH/Terminal panel:

```bash
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 6) Cron Scheduler (WAJIB)
Tambahkan cron (setiap menit):

```bash
* * * * * /usr/bin/php /home/<user>/sistem_ujian/artisan schedule:run >> /dev/null 2>&1
```

Tanpa cron scheduler, lifecycle exam/attempt bisa tidak sinkron.

## 7) Smoke Test
- Login admin sukses.
- Publish exam sukses.
- Peserta bisa mulai exam pada window waktu valid.
- Result peserta tidak bisa diakses sebelum exam finished.
- Operator tidak bisa publish exam.

## Troubleshooting Cepat
- 500 error setelah upload:
  - cek `APP_KEY` sudah terisi.
  - cek versi PHP >= 8.2.
  - cek path di `public_html/index.php` benar.
- CSS tidak muncul:
  - pastikan `public/build` ikut terupload.
  - pastikan file `public/hot` tidak ikut terupload.
- Session error:
  - pastikan tabel session ada (`php artisan migrate --force`).
