#!/bin/bash
set -e

echo "🚀 CONSERVICOS — starting..."

# Wait for MySQL with timeout to avoid endless startup loops.
MAX_MYSQL_WAIT_SECONDS="${MAX_MYSQL_WAIT_SECONDS:-90}"
SLEEP_SECONDS=3
MAX_RETRIES=$((MAX_MYSQL_WAIT_SECONDS / SLEEP_SECONDS))
ATTEMPT=0

echo "⏳ Waiting for MySQL at ${DB_HOST}:${DB_PORT:-3306} (timeout: ${MAX_MYSQL_WAIT_SECONDS}s)..."
until mysql --protocol=TCP --skip-ssl -h "${DB_HOST}" -P "${DB_PORT:-3306}" -u "${DB_USERNAME}" --password="${DB_PASSWORD}" -e "SELECT 1" >/dev/null 2>&1; do
    ATTEMPT=$((ATTEMPT + 1))
    if [ "${ATTEMPT}" -ge "${MAX_RETRIES}" ]; then
        echo "❌ MySQL not reachable after ${MAX_MYSQL_WAIT_SECONDS}s."
        echo "   Check DB_HOST/DB_PORT/DB_USERNAME/DB_PASSWORD and MySQL logs."
        exit 1
    fi
    echo "  MySQL not ready — retrying in ${SLEEP_SECONDS}s... (${ATTEMPT}/${MAX_RETRIES})"
    sleep "${SLEEP_SECONDS}"
done
echo "✅ MySQL ready."

# Run migrations
echo "📦 Running migrations..."
php artisan migrate --force --no-interaction

# Seed on first boot only (check if companies table is empty)
COMPANY_COUNT=$(php artisan tinker --execute="echo \App\Models\Company::count();" 2>/dev/null | grep -E '^[0-9]+$' | tail -1 || echo "1")
if [ "$COMPANY_COUNT" = "0" ]; then
    echo "🌱 Seeding database..."
    php artisan db:seed --force --no-interaction
fi

# Publish Filament assets (CSS/JS)
echo "🎨 Publishing Filament assets..."
php artisan filament:assets || true

# Cache for performance
echo "⚡ Caching config / routes / views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Storage symlink
php artisan storage:link --force 2>/dev/null || true

# Fix permissions
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo "✅ Application ready — http://localhost:${APP_PORT:-9991}/admin"
echo ""

# Hand off to the CMD (php-fpm or artisan command)
exec "$@"
