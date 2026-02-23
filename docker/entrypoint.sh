#!/bin/bash
set -e

echo "────────────────────────────────────────────"
echo "  Todo App – Container Startup"
echo "────────────────────────────────────────────"
cd /var/www/html

# ── 1. Always sync .env from .env.docker ─────────────────────
#       This ensures config changes in the image take effect.
echo "[boot] Writing .env from .env.docker…"
cp .env.docker .env

# ── 2. Generate APP_KEY if missing ───────────────────────────
app_key_value="$(grep -m1 '^APP_KEY=' .env | cut -d '=' -f2- | tr -d '\r')"
if [ -z "$app_key_value" ]; then
    echo "[boot] Generating application key…"
    php artisan key:generate --force --ansi
fi

# ── 3. Wait for PostgreSQL ───────────────────────────────────
echo "[boot] Waiting for PostgreSQL at ${DB_HOST:-db}:${DB_PORT:-5432}…"
until pg_isready -h "${DB_HOST:-db}" -p "${DB_PORT:-5432}" \
                 -U "${DB_USERNAME:-todo_user}" -d "${DB_DATABASE:-todo_app}" &>/dev/null; do
    echo "  … not ready yet, retrying in 3 s"
    sleep 3
done
echo "[boot] PostgreSQL is ready."

# ── 4. Run migrations ────────────────────────────────────────
echo "[boot] Running migrations…"
php artisan migrate --force --ansi

# ── 5. Ensure storage directories exist (volume may be empty) ─
echo "[boot] Ensuring storage structure…"
mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# ── 6. Cache config & routes (after key is set) ──────────────
echo "[boot] Warming caches…"
php artisan config:cache --ansi
php artisan route:cache  --ansi

echo "────────────────────────────────────────────"
echo "  Boot complete – starting PHP-FPM"
echo "────────────────────────────────────────────"

exec "$@"
