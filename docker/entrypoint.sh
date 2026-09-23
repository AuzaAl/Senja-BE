#!/bin/sh
set -e

echo "[senja-be] Starting container..."

# ---------------------------------------------------------------------------
# 1. Wait for the database to accept connections (compose may start MySQL
#    after this container). Skipped when a non-DB driver is configured.
# ---------------------------------------------------------------------------
if [ -n "${DB_HOST:-}" ]; then
    echo "[senja-be] Waiting for database at ${DB_HOST}:${DB_PORT:-3306}..."
    i=0
    until php -r '
        $host = getenv("DB_HOST");
        $port = getenv("DB_PORT") ?: "3306";
        $db   = getenv("DB_DATABASE");
        $user = getenv("DB_USERNAME");
        $pass = getenv("DB_PASSWORD");
        try {
            new PDO("mysql:host={$host};port={$port};dbname={$db}", $user, $pass);
            exit(0);
        } catch (Throwable $e) {
            exit(1);
        }
    '; do
        i=$((i + 1))
        if [ "$i" -ge 30 ]; then
            echo "[senja-be] Database not reachable after 30 attempts, continuing anyway."
            break
        fi
        sleep 2
    done
fi

# ---------------------------------------------------------------------------
# 2. Storage scaffolding + permissions.
# ---------------------------------------------------------------------------
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# ---------------------------------------------------------------------------
# 3. Passport keys — generate once if missing (never overwrite existing).
# ---------------------------------------------------------------------------
if [ ! -f storage/oauth-private.key ] || [ ! -f storage/oauth-public.key ]; then
    echo "[senja-be] Generating Passport encryption keys..."
    php artisan passport:keys --force
fi
chmod 600 storage/oauth-*.key 2>/dev/null || true

# ---------------------------------------------------------------------------
# 4. Application key — must be provided via env in production.
# ---------------------------------------------------------------------------
if [ -z "${APP_KEY:-}" ]; then
    echo "[senja-be] WARNING: APP_KEY is not set. Run 'php artisan key:generate' or set it in .env."
fi

# ---------------------------------------------------------------------------
# 5. Migrations + role/permission seeding (idempotent).
# ---------------------------------------------------------------------------
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "[senja-be] Running migrations..."
    if ! php artisan migrate --force; then
        echo "[senja-be] ERROR: migrations failed. Aborting startup."
        exit 1
    fi

    if [ "${RUN_SEEDER:-true}" = "true" ]; then
        echo "[senja-be] Seeding roles & permissions..."
        php artisan db:seed --class=RolePermissionSeeder --force
    fi
fi

# ---------------------------------------------------------------------------
# 6. Public storage symlink for uploaded images.
# ---------------------------------------------------------------------------
if [ ! -L public/storage ]; then
    php artisan storage:link || true
fi

# ---------------------------------------------------------------------------
# 7. Cache config/routes/views for production performance.
# ---------------------------------------------------------------------------
if [ "${APP_ENV:-production}" = "production" ]; then
    echo "[senja-be] Caching configuration..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

echo "[senja-be] Startup complete. Handing off to supervisord."
exec "$@"
