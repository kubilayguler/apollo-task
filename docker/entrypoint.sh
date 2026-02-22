#!/bin/bash
set -e

echo "────────────────────────────────────────────"
echo "  Todo App – Container Startup"
echo "────────────────────────────────────────────"
cd /var/www/html

# ── 1. Ensure .env exists ────────────────────────────────────
if [ ! -f ".env" ]; then
    echo "[boot] .env not found – copying .env.docker"
    cp .env.docker .env 2>/dev/null || cp .env.example .env
fi

# ── 2. Generate APP_KEY if missing ──────────────────────────
if [ -z "$(grep '^APP_KEY=.\+' .env)" ]; then
    echo "[boot] Generating application key…"
    php artisan key:generate --force --ansi
fi

# ── 3. Wait for PostgreSQL to accept connections ─────────────
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

# ── 5. Clear & warm caches ───────────────────────────────────
echo "[boot] Warming caches…"
php artisan config:cache  --ansi
php artisan route:cache   --ansi
php artisan view:cache    --ansi

# ── 6. Fix storage permissions ───────────────────────────────
chown -R www-data:www-data storage bootstrap/cache

echo "────────────────────────────────────────────"
echo "  Boot complete – starting PHP-FPM"
echo "────────────────────────────────────────────"

exec "$@"
