# Panduan Deployment — Senja BE

Dokumen ini menjelaskan cara men-deploy API Laravel ke environment **staging** dan **production**.

## Environment

| Environment | File env | Domain API |
|---|---|---|
| Local | `.env` | http://localhost:8000 |
| Staging | `.env.staging.example` | https://staging-api.senja.sabirudigital.id |
| Production | `.env.production.example` | https://api.senja.sabirudigital.id |

> **Catatan:** file `.env.staging.example` dan `.env.production.example` hanya berisi
> *template* (tanpa kredensial). Salin menjadi `.env` di server masing-masing,
> dan **jangan commit file `.env` yang berisi kredensial asli**.

## Prasyarat Server

- PHP >= 8.3 (disarankan 8.4) dengan ekstensi: `ctype`, `curl`, `dom`, `fileinfo`,
  `filter`, `hash`, `mbstring`, `openssl`, `pcre`, `pdo`, `pdo_mysql`,
  `session`, `tokenizer`, `xml`
- Composer 2.x
- MySQL 8.x
- Redis (disarankan, untuk produksi)
- Web server (Nginx/Apache) — document root diarahkan ke folder `public/`

## Langkah Deploy (staging & production)

```bash
# 1. Ambil kode terbaru
git pull origin main

# 2. Pasang dependensi tanpa dev
composer install --no-dev --optimize-autoloader

# 3. Siapkan environment
cp .env.staging.example .env      # atau .env.production.example
php artisan key:generate  # hanya jika APP_KEY masih kosong

# 4. Isi kredensial di .env (DB, redis, mail, passport client)

# 5. Migrasi database
php artisan migrate --force

# 6. Pasang Passport (sekali saja per environment)
php artisan passport:keys
php artisan passport:client --password --name="Senja CMS"
# Salin client id & secret ke PASSPORT_PASSWORD_CLIENT_ID / _SECRET

# 7. Symlink storage untuk gambar publik
php artisan storage:link

# 8. Optimasi untuk produksi
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

## Setelah Deploy

```bash
# Seeder role dasar (admin, editor, viewer)
php artisan db:seed --class=RoleSeeder --force
```

## Update / Rilis Berikutnya

```bash
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan queue:restart
```

## Checklist Keamanan Produksi

- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] `APP_KEY` sudah di-generate
- [ ] Kredensial DB/Redis/Mail kuat & tidak dipakai bersama staging
- [ ] `CORS_ALLOWED_ORIGINS` hanya domain resmi
- [ ] HTTPS aktif (sertifikat valid)
- [ ] Rate limiting API aktif
- [ ] Backup database terjadwal
- [ ] `storage/` dan `bootstrap/cache/` writable oleh web server
- [ ] File `.env` tidak dapat diakses publik

## CI/CD

Belum ada pipeline otomatis (`.github/workflows`). Rencana:
- **Staging:** deploy otomatis saat push ke branch `develop`
- **Production:** deploy saat push tag `v*` atau merge ke `main`

(Tugas ini tercatat di list `09 - QA & Deployment`.)
