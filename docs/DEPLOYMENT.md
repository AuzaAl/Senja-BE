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

## Deployment dengan Docker

Repositori ini menyertakan `Dockerfile` (PHP 8.4 FPM + Nginx dalam satu image
via Supervisor) dan `docker-compose.yml` (app + MySQL 8 + Redis 7) untuk
menjalankan API secara mandiri.

### Struktur berkas

| Berkas | Fungsi |
|---|---|
| `Dockerfile` | Multi-stage build: install dependensi Composer → rakit app → image runtime (nginx + php-fpm + supervisor + ekstensi `redis`) |
| `docker-compose.yml` | App + MySQL + Redis, healthcheck, volume persisten |
| `.dockerignore` | Mengecualikan `.env`, `vendor/`, `node_modules/`, dsb. dari build context |
| `docker/nginx.conf` | Konfigurasi web server (root `public/`) |
| `docker/php-fpm.conf` | Tuning pool PHP-FPM |
| `docker/supervisord.conf` | Menjalankan nginx + php-fpm dalam satu container |
| `docker/entrypoint.sh` | Menunggu DB, generate key Passport, migrasi, seeder, cache config, symlink storage |

### Menjalankan (lokal / server)

```bash
# 1. Siapkan env untuk container
cp .env.production.example .env
# isi APP_KEY, kredensial DB, Redis, dan pasangan passport client

# 2. Bangun & jalankan seluruh stack
docker compose up -d --build

# 3. Pantau startup (migrasi + seeder berjalan otomatis)
docker compose logs -f senja_be

# 4. Buat password-grant client Passport (sekali saja)
docker compose exec senja_be php artisan passport:client --password --name="Senja CMS"
# Salin client id/secret ke PASSPORT_PASSWORD_CLIENT_ID / _SECRET di .env,
# lalu: docker compose restart senja_be
```

API tersedia di `http://localhost:${HOST_PORT:-13053}/api/v1`.

### Variabel environment penting

| Variabel | Default | Keterangan |
|---|---|---|
| `HOST_PORT` | `13053` | Port host yang dipetakan ke port 80 container |
| `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | `senja_be` / `senja` / `senja` | Kredensial MySQL container |
| `DB_ROOT_PASSWORD` | `root` | Password root MySQL container |
| `RUN_MIGRATIONS` | `true` | Jalankan `migrate --force` saat start |
| `RUN_SEEDER` | `true` | Jalankan `RolePermissionSeeder` saat start |
| `REDIS_HOST` | `redis` | Di-override otomatis oleh compose |

> **Catatan:** kunci Passport (`storage/oauth-*.key`) dan gambar hasil upload
> disimpan pada volume `senja_be_storage` sehingga tetap ada saat container
> di-rebuild.

### Operasional

```bash
docker compose ps                 # status service
docker compose logs -f senja_be   # log app
docker compose exec senja_be php artisan migrate --force
docker compose exec senja_be php artisan db:seed --class=RolePermissionSeeder --force
docker compose down               # hentikan (data tetap)
docker compose down -v            # hentikan + hapus volume (reset total)
```

### Deployment tanpa Docker

Tetap didukung mengikuti langkah “Langkah Deploy” di atas (PHP-FPM + Nginx +
MySQL + Redis yang sudah tersedia di server).
