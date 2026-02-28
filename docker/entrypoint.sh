#!/bin/bash
set -e

echo "🚀 CONSERVICOS — starting..."

# Wait for MySQL to be ready using mysqladmin (more reliable than PHP PDO)
echo "⏳ Waiting for MySQL at ${DB_HOST}:${DB_PORT:-3306}..."
until mysqladmin ping -h "${DB_HOST}" -P "${DB_PORT:-3306}" -u "${DB_USERNAME}" --password="${DB_PASSWORD}" --silent 2>/dev/null; do
    echo "  MySQL not ready — retrying in 3s..."
    sleep 3
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
