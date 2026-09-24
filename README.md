# Senja BE — Laravel API (Company Profile)

API backend untuk website company profile **Senja**, dipakai oleh dua frontend:

| App | Repo | Default URL |
|---|---|---|
| Landing page | `Senja-FE` (Next.js) | http://localhost:3000 |
| Admin CMS | `Senja-CMS` (Next.js) | http://localhost:3001 |
| API ini | `be-senja-laravel` | http://localhost:8000/api/v1 |

**Stack:** Laravel 13 · PHP 8.4 · MySQL 8 · Redis 7 · Laravel Passport (auth) · spatie/laravel-permission (RBAC)

> 📖 Dokumentasi endpoint lengkap: [`docs/openapi.yaml`](docs/openapi.yaml)
> 🚀 Panduan deploy staging/production: [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md)

---

## ⚡ Mulai Cepat (5 menit)

### Opsi A — Pakai Docker (paling gampang, disarankan)

Semua sudah otomatis: MySQL, Redis, migrasi, seeder, dan Passport keys.
Yang perlu kamu lakukan cuma **1 langkah manual** (bikin client Passport) di bawah.

```bash
# 1. Siapkan file environment
cp .env.production.example .env
#    lalu isi: APP_KEY, kredensial DB, Redis

# 2. Jalankan seluruh stack
docker compose up -d --build

# 3. Tunggu startup (migrasi + seeder jalan otomatis, ±15 detik)
docker compose logs -f senja_be
#    → tunggu sampai muncul "Startup complete. Handing off to supervisord."

# 4. ⚠️ WAJIB: bikin client Passport (lihat bagian "Passport" di bawah)
docker compose exec senja_be php artisan passport:client --password --name="Senja CMS"
#    → copy Client ID + Secret ke .env, lalu:
docker compose restart senja_be
```

### Opsi B — Manual tanpa Docker

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed

# ⚠️ WAJIB: Passport (lihat bagian "Passport" di bawah)
php artisan passport:keys
php artisan passport:client --password --name="Senja CMS"
php artisan storage:link

php artisan serve                 # http://localhost:8000
```

Verifikasi API hidup:
```bash
curl http://localhost:8000/api/v1/health
# → {"status":"ok","version":"v1"}
```

---

## 🔑 Passport — Langkah yang Paling Sering Bikin Error

> **Ini penyebab nomor satu login gagal dengan pesan `"Gagal menerbitkan token."`**

Passport butuh **3 hal**. Kalau salah satu kurang, login **selalu** gagal
(walau email & password kamu benar).

| # | Yang dibutuhkan | Perintah |
|---|---|---|
| 1 | Encryption keys | `php artisan passport:keys` |
| 2 | **Izin file key = 600** | `chmod 600 storage/oauth-*.key` |
| 3 | **Password-grant client** | `php artisan passport:client --password` |

`php artisan key:generate` **TIDAK** membuat ketiganya — itu cuma bikin `APP_KEY`.
Inilah kenapa "sudah key:generate" tapi login tetap gagal.

### Langkah lengkap (manual)

```bash
# 1. Bikin encryption keys (sekali saja per environment)
php artisan passport:keys

# 2. Betulkan izin file key — Passport MENOLAK key dengan izin 775.
#    Wajib 600 atau 660. Tanpa ini, login gagal sebelum client dicek.
chmod 600 storage/oauth-private.key storage/oauth-public.key

# 3. Bikin password-grant client (sekali saja per environment)
php artisan passport:client --password --name="Senja CMS"
#    → catat Client ID & Client Secret yang muncul

# 4. Salin nilainya ke .env
#    PASSPORT_PASSWORD_CLIENT_ID=<client id>
#    PASSPORT_PASSWORD_CLIENT_SECRET=<client secret>

# 5. Muat ulang config
php artisan config:clear
```

### Langkah lengkap (Docker)

```bash
# Di dalam container — entrypoint sudah otomatis menangani keys + chmod 600.
# Yang perlu kamu lakukan hanya bikin client-nya:
docker compose exec senja_be php artisan passport:client --password --name="Senja CMS"

# Salin Client ID + Secret ke .env, lalu recreate container
# (restart saja TIDAK cukup — env_file hanya dibaca saat container dibuat)
docker compose up -d senja_be
```

### ❗ Kalau menjalankan `migrate:fresh`

`migrate:fresh` **menghapus semua tabel, termasuk `oauth_clients`.**
Artinya client Passport kamu **hilang** dan login akan gagal lagi.

Setiap kali reset database, **ulangi langkah 3–5 di atas:**

```bash
php artisan migrate:fresh --seed
php artisan passport:client --password --name="Senja CMS"   # bikin ulang
# update .env dengan client id/secret baru → php artisan config:clear
```

> **Kenapa tidak otomatis?** Ini memang disengaja. Auto-generate client di setiap
> startup berisiko: client menumpuk, secret berubah tiap restart, dan kadang
> ter-cetak ke log server. Membuatnya manual = aman & terkontrol.

### Cek cepat: apakah Passport sudah beres?

```bash
# 1. Client ada di DB?
php artisan tinker --execute="echo \DB::table('oauth_clients')->count();"   # harus ≥ 1

# 2. Izin key benar?
stat -c '%a %n' storage/oauth-private.key        # harus 600

# 3. Config terbaca?
php artisan tinker --execute="echo config('services.passport.password_client_id');"  # tidak kosong

# 4. Login jalan?
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@senja.id","password":"password"}'
# → {"message":"Login berhasil.", ...}
```

---

## 👤 Akun Testing (dari seeder)

| Role | Email | Password | Hak akses |
|---|---|---|---|
| admin | `admin@senja.id` | `password` | Semua, termasuk manajemen user |
| editor | `editor@senja.id` | `password` | CRUD konten, tanpa manajemen user |
| viewer | `viewer@senja.id` | `password` | Hanya baca konten |

> ⚠️ **Ganti password ini sebelum production!** Akun di atas hanya untuk development/testing.

---

## 🔒 Keamanan — Panduan Deploy AMAN

### ⛔ JANGAN PERNAH commit / kirim file `.env`

`.env` berisi **semua rahasia**: `APP_KEY`, password DB, dan **Passport client secret**.
Kalau bocor, orang bisa login sebagai admin dan membaca data.

```
❌ JANGAN:   commit .env · kirim .env via WhatsApp/email · zip folder termasuk .env
✅ YANG BENAR: commit .env.example saja · isi rahasia langsung di server
```

`.env` sudah aman dari git — sudah terdaftar di `.gitignore` dan **belum pernah** ter-commit.

### Checklist keamanan sebelum production

- [ ] `APP_ENV=production` dan `APP_DEBUG=false` — jangan tampilkan stack trace ke publik
- [ ] `php artisan key:generate` (APP_KEY unik per environment)
- [ ] **Ganti semua akun testing** (`admin@senja.id` dkk) atau hapus
- [ ] Password DB, Redis, dan Mail **kuat & berbeda** dari staging
- [ ] **Izin file key Passport = 600** (`chmod 600 storage/oauth-*.key`)
- [ ] `PASSPORT_PASSWORD_CLIENT_SECRET` hanya ada di `.env` server (jangan di kode/git)
- [ ] CORS hanya untuk domain resmi
- [ ] HTTPS aktif + sertifikat valid
- [ ] `storage/` dan `bootstrap/cache/` writable oleh web server
- [ ] File `.env` **tidak bisa diakses publik** (pastikan document root → `public/`)
- [ ] Backup database terjadwal

### Untuk rekan yang melanjutkan proyek ini

Kasih ini — **jangan `.env`**:

| ✅ Berikan | ❌ Jangan berikan |
|---|---|
| Seluruh source code | File `.env` |
| `.env.example` (template, tanpa rahasia) | Isi `APP_KEY` / client secret |
| `README.md` ini + `docs/` | Password DB / akun produksi |
| Akun testing (kalau perlu) | Kredensial server |

Rekanmu cukup ikut **"Mulai Cepat"** di atas — dia akan membuat rahasianya sendiri.

---

## 🧪 Testing

```bash
php artisan test          # 78 test, 242 assertions (mencakup auth, RBAC, CRUD, upload)
./vendor/bin/pint --test  # cek code style
```

---

## 📚 Struktur Singkat

```
app/Http/Controllers/Api/V1/   Controller API (Auth, Hero, About, Partner, Project,
                               ContactInquiry, User, Upload)
app/Http/Requests/             Validasi input
app/Http/Resources/            Bentuk response JSON
app/Models/                    Eloquent model
routes/api/v1.php              Definisi route (prefix /api/v1 otomatis)
database/seeders/              Data awal (roles, akun, konten contoh)
docs/openapi.yaml              Dokumentasi API (OpenAPI 3.0)
docs/DEPLOYMENT.md             Panduan deploy staging/production
```

**Rate limit:** `/auth/login` & `/auth/refresh` = 10 req/menit · `POST /contact-inquiries` = 30 req/menit.
