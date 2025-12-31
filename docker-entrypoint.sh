#!/bin/sh
set -e

# .env dosyasını oluşturma fonksiyonu
setup_env() {
    # APP_KEY kontrolü - placeholder ise boşalt
    APP_KEY_VALUE="$APP_KEY"
    if echo "$APP_KEY" | grep -qE "(base64:\.\.\.|oluşturulacak|ilk deploy)"; then
        APP_KEY_VALUE=""
    fi

    # .env dosyasını oluştur
    # APP_KEY her zaman eklenmeli (boş olsa bile), Laravel bunu arıyor
    cat > /var/www/html/.env <<EOF
APP_NAME="${APP_NAME:-Laravel}"
APP_ENV=${APP_ENV:-production}
APP_KEY=${APP_KEY_VALUE}
APP_DEBUG=${APP_DEBUG:-false}
APP_URL=${APP_URL:-http://localhost}

LOG_CHANNEL=${LOG_CHANNEL:-stack}
LOG_LEVEL=${LOG_LEVEL:-error}

DB_CONNECTION=${DB_CONNECTION:-pgsql}
DB_HOST=${DB_HOST:-127.0.0.1}
DB_PORT=${DB_PORT:-5432}
DB_DATABASE=${DB_DATABASE:-laravel}
DB_USERNAME=${DB_USERNAME:-root}
DB_PASSWORD=${DB_PASSWORD:-}

BROADCAST_CONNECTION=${BROADCAST_CONNECTION:-log}
FILESYSTEM_DISK=${FILESYSTEM_DISK:-local}
QUEUE_CONNECTION=${QUEUE_CONNECTION:-sync}

CACHE_DRIVER=${CACHE_DRIVER:-file}
CACHE_STORE=${CACHE_STORE:-${CACHE_DRIVER:-file}}

SESSION_DRIVER=${SESSION_DRIVER:-file}
SESSION_LIFETIME=${SESSION_LIFETIME:-120}

REDIS_HOST=${REDIS_HOST:-127.0.0.1}
REDIS_PASSWORD=${REDIS_PASSWORD:-null}
REDIS_PORT=${REDIS_PORT:-6379}

MAIL_MAILER=${MAIL_MAILER:-smtp}
MAIL_HOST=${MAIL_HOST:-mailpit}
MAIL_PORT=${MAIL_PORT:-1025}
MAIL_USERNAME=${MAIL_USERNAME:-null}
MAIL_PASSWORD=${MAIL_PASSWORD:-null}
MAIL_ENCRYPTION=${MAIL_ENCRYPTION:-null}
MAIL_FROM_ADDRESS=${MAIL_FROM_ADDRESS:-"hello@example.com"}
MAIL_FROM_NAME="${MAIL_FROM_NAME:-${APP_NAME}}"

VITE_APP_NAME="${APP_NAME}"

LITELLM_BASE_URL=${LITELLM_BASE_URL:-}
LITELLM_MASTER_KEY=${LITELLM_MASTER_KEY:-}

STRIPE_KEY=${STRIPE_KEY:-}
STRIPE_SECRET=${STRIPE_SECRET:-}
STRIPE_WEBHOOK_SECRET=${STRIPE_WEBHOOK_SECRET:-}

SANCTUM_STATEFUL_DOMAINS=${SANCTUM_STATEFUL_DOMAINS:-}
EOF

    # APP_KEY yoksa veya placeholder ise oluştur
    if [ -z "$APP_KEY_VALUE" ]; then
        echo "APP_KEY oluşturuluyor..."
        if php artisan key:generate --force --no-interaction; then
            echo "APP_KEY başarıyla oluşturuldu."
        else
            echo "UYARI: APP_KEY oluşturulamadı, ancak devam ediliyor..."
        fi
    else
        echo "APP_KEY zaten ayarlanmış."
    fi

    # İzinleri ayarla (hata olsa bile devam et)
    chown -R www-data:www-data /var/www/html || true
    chmod -R 755 /var/www/html/storage || true
    chmod -R 755 /var/www/html/bootstrap/cache || true
}

# Eğer .env dosyası yoksa oluştur
if [ ! -f /var/www/html/.env ]; then
    echo ".env dosyası bulunamadı, oluşturuluyor..."
    setup_env
fi

# Eğer script doğrudan çalıştırılıyorsa (setup komutu olarak)
if [ "$1" = "setup" ] || [ "$1" = "setup-env" ]; then
    echo "Environment setup çalıştırılıyor..."
    setup_env
    exit 0
fi

# Orijinal komutu çalıştır
exec "$@"

