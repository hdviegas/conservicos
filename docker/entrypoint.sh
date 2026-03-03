#!/bin/bash
set -e

echo "🚀 CONSERVICOS — starting..."

# Sync baked public assets to shared volume (nginx reads from this volume in prod)
if [ -d /var/www/html/public-build ]; then
    echo "📂 Syncing public assets to shared volume..."
    cp -a /var/www/html/public-build/. /var/www/html/public/
fi

# Fix permissions (may fail silently if not owner)
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# Storage symlink (safe to run always)
php artisan storage:link --force 2>/dev/null || true

# If this is a queue/scheduler worker, skip DB setup and jump to exec
if [ "$1" != "php-fpm" ]; then
    echo "⚡ Worker mode — skipping DB bootstrap"
    exec "$@"
fi

# ─── DB bootstrap (only for php-fpm entrypoint) ────────────────────────────

MAX_MYSQL_WAIT_SECONDS="${MAX_MYSQL_WAIT_SECONDS:-90}"
SLEEP_SECONDS=3
MAX_RETRIES=$((MAX_MYSQL_WAIT_SECONDS / SLEEP_SECONDS))
ATTEMPT=0

echo "⏳ Waiting for MySQL at ${DB_HOST}:${DB_PORT:-3306} (timeout: ${MAX_MYSQL_WAIT_SECONDS}s)..."
until mysql --protocol=TCP --skip-ssl -h "${DB_HOST}" -P "${DB_PORT:-3306}" -u "${DB_USERNAME}" --password="${DB_PASSWORD}" -e "SELECT 1" >/dev/null 2>&1; do
    ATTEMPT=$((ATTEMPT + 1))
    if [ "${ATTEMPT}" -ge "${MAX_RETRIES}" ]; then
        echo "❌ MySQL not reachable after ${MAX_MYSQL_WAIT_SECONDS}s — starting php-fpm anyway."
        echo "   Check DB_HOST/DB_PORT/DB_USERNAME/DB_PASSWORD and MySQL logs."
        exec "$@"
    fi
    echo "  MySQL not ready — retrying in ${SLEEP_SECONDS}s... (${ATTEMPT}/${MAX_RETRIES})"
    sleep "${SLEEP_SECONDS}"
done
echo "✅ MySQL ready."

# Run migrations (non-fatal: app starts even if migration fails)
echo "📦 Running migrations..."
php artisan migrate --force --no-interaction || echo "⚠️  Migration failed — check logs"

# Seed on first boot only
COMPANY_COUNT=$(php artisan tinker --execute="echo \App\Models\Company::count();" 2>/dev/null | grep -E '^[0-9]+$' | tail -1 || echo "1")
if [ "$COMPANY_COUNT" = "0" ]; then
    echo "🌱 Seeding database..."
    php artisan db:seed --force --no-interaction || echo "⚠️  Seed failed — check logs"
fi

# Publish Filament assets
echo "🎨 Publishing Filament assets..."
php artisan filament:assets 2>/dev/null || true

# Re-sync public after filament:assets may have added files
if [ -d /var/www/html/public-build ]; then
    cp -a /var/www/html/public/build/. /var/www/html/public-build/build/ 2>/dev/null || true
fi

# Cache for performance
echo "⚡ Caching config / routes / views..."
php artisan config:cache  || true
php artisan route:cache   || true
php artisan view:cache    || true
php artisan event:cache   || true

echo "✅ Application ready — http://localhost:${APP_PORT:-9991}/admin"
echo ""

exec "$@"
