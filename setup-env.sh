#!/bin/sh
# Container içinde manuel .env oluşturma scripti

# .env dosyasını oluştur
cat > /var/www/html/.env <<'ENVEOF'
APP_NAME="${APP_NAME:-CodexFlow SaaS}"
APP_ENV="${APP_ENV:-production}"
APP_KEY=
APP_DEBUG="${APP_DEBUG:-false}"
APP_URL="${APP_URL:-http://localhost}"

LOG_CHANNEL="${LOG_CHANNEL:-stack}"
LOG_LEVEL="${LOG_LEVEL:-error}"

DB_CONNECTION="${DB_CONNECTION:-pgsql}"
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-5432}"
DB_DATABASE="${DB_DATABASE:-laravel}"
DB_USERNAME="${DB_USERNAME:-root}"
DB_PASSWORD="${DB_PASSWORD:-}"

BROADCAST_CONNECTION="${BROADCAST_CONNECTION:-log}"
FILESYSTEM_DISK="${FILESYSTEM_DISK:-local}"
QUEUE_CONNECTION="${QUEUE_CONNECTION:-sync}"

CACHE_DRIVER="${CACHE_DRIVER:-file}"
SESSION_DRIVER="${SESSION_DRIVER:-file}"
SESSION_LIFETIME="${SESSION_LIFETIME:-120}"

REDIS_HOST="${REDIS_HOST:-127.0.0.1}"
REDIS_PASSWORD="${REDIS_PASSWORD:-null}"
REDIS_PORT="${REDIS_PORT:-6379}"

MAIL_MAILER="${MAIL_MAILER:-smtp}"
MAIL_HOST="${MAIL_HOST:-mailpit}"
MAIL_PORT="${MAIL_PORT:-1025}"
MAIL_USERNAME="${MAIL_USERNAME:-null}"
MAIL_PASSWORD="${MAIL_PASSWORD:-null}"
MAIL_ENCRYPTION="${MAIL_ENCRYPTION:-null}"
MAIL_FROM_ADDRESS="${MAIL_FROM_ADDRESS:-hello@example.com}"
MAIL_FROM_NAME="${MAIL_FROM_NAME:-${APP_NAME}}"

VITE_APP_NAME="${APP_NAME}"

LITELLM_BASE_URL="${LITELLM_BASE_URL:-}"
LITELLM_MASTER_KEY="${LITELLM_MASTER_KEY:-}"

STRIPE_KEY="${STRIPE_KEY:-}"
STRIPE_SECRET="${STRIPE_SECRET:-}"
STRIPE_WEBHOOK_SECRET="${STRIPE_WEBHOOK_SECRET:-}"

SANCTUM_STATEFUL_DOMAINS="${SANCTUM_STATEFUL_DOMAINS:-}"
ENVEOF

# Ortam değişkenlerini .env dosyasına yaz
if [ -n "$APP_NAME" ]; then sed -i "s|APP_NAME=.*|APP_NAME=\"$APP_NAME\"|" /var/www/html/.env; fi
if [ -n "$APP_ENV" ]; then sed -i "s|APP_ENV=.*|APP_ENV=$APP_ENV|" /var/www/html/.env; fi
if [ -n "$APP_DEBUG" ]; then sed -i "s|APP_DEBUG=.*|APP_DEBUG=$APP_DEBUG|" /var/www/html/.env; fi
if [ -n "$APP_URL" ]; then sed -i "s|APP_URL=.*|APP_URL=$APP_URL|" /var/www/html/.env; fi
if [ -n "$DB_CONNECTION" ]; then sed -i "s|DB_CONNECTION=.*|DB_CONNECTION=$DB_CONNECTION|" /var/www/html/.env; fi
if [ -n "$DB_HOST" ]; then sed -i "s|DB_HOST=.*|DB_HOST=$DB_HOST|" /var/www/html/.env; fi
if [ -n "$DB_PORT" ]; then sed -i "s|DB_PORT=.*|DB_PORT=$DB_PORT|" /var/www/html/.env; fi
if [ -n "$DB_DATABASE" ]; then sed -i "s|DB_DATABASE=.*|DB_DATABASE=$DB_DATABASE|" /var/www/html/.env; fi
if [ -n "$DB_USERNAME" ]; then sed -i "s|DB_USERNAME=.*|DB_USERNAME=$DB_USERNAME|" /var/www/html/.env; fi
if [ -n "$DB_PASSWORD" ]; then sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=$DB_PASSWORD|" /var/www/html/.env; fi
if [ -n "$REDIS_HOST" ]; then sed -i "s|REDIS_HOST=.*|REDIS_HOST=$REDIS_HOST|" /var/www/html/.env; fi
if [ -n "$REDIS_PASSWORD" ]; then sed -i "s|REDIS_PASSWORD=.*|REDIS_PASSWORD=$REDIS_PASSWORD|" /var/www/html/.env; fi
if [ -n "$REDIS_PORT" ]; then sed -i "s|REDIS_PORT=.*|REDIS_PORT=$REDIS_PORT|" /var/www/html/.env; fi
if [ -n "$CACHE_DRIVER" ]; then sed -i "s|CACHE_DRIVER=.*|CACHE_DRIVER=$CACHE_DRIVER|" /var/www/html/.env; fi
if [ -n "$SESSION_DRIVER" ]; then sed -i "s|SESSION_DRIVER=.*|SESSION_DRIVER=$SESSION_DRIVER|" /var/www/html/.env; fi
if [ -n "$QUEUE_CONNECTION" ]; then sed -i "s|QUEUE_CONNECTION=.*|QUEUE_CONNECTION=$QUEUE_CONNECTION|" /var/www/html/.env; fi
if [ -n "$LITELLM_BASE_URL" ]; then sed -i "s|LITELLM_BASE_URL=.*|LITELLM_BASE_URL=$LITELLM_BASE_URL|" /var/www/html/.env; fi
if [ -n "$LITELLM_MASTER_KEY" ]; then sed -i "s|LITELLM_MASTER_KEY=.*|LITELLM_MASTER_KEY=$LITELLM_MASTER_KEY|" /var/www/html/.env; fi
if [ -n "$MAIL_MAILER" ]; then sed -i "s|MAIL_MAILER=.*|MAIL_MAILER=$MAIL_MAILER|" /var/www/html/.env; fi
if [ -n "$MAIL_HOST" ]; then sed -i "s|MAIL_HOST=.*|MAIL_HOST=$MAIL_HOST|" /var/www/html/.env; fi
if [ -n "$MAIL_PORT" ]; then sed -i "s|MAIL_PORT=.*|MAIL_PORT=$MAIL_PORT|" /var/www/html/.env; fi
if [ -n "$MAIL_USERNAME" ]; then sed -i "s|MAIL_USERNAME=.*|MAIL_USERNAME=$MAIL_USERNAME|" /var/www/html/.env; fi
if [ -n "$MAIL_PASSWORD" ]; then sed -i "s|MAIL_PASSWORD=.*|MAIL_PASSWORD=$MAIL_PASSWORD|" /var/www/html/.env; fi
if [ -n "$MAIL_ENCRYPTION" ]; then sed -i "s|MAIL_ENCRYPTION=.*|MAIL_ENCRYPTION=$MAIL_ENCRYPTION|" /var/www/html/.env; fi
if [ -n "$MAIL_FROM_ADDRESS" ]; then sed -i "s|MAIL_FROM_ADDRESS=.*|MAIL_FROM_ADDRESS=$MAIL_FROM_ADDRESS|" /var/www/html/.env; fi
if [ -n "$MAIL_FROM_NAME" ]; then sed -i "s|MAIL_FROM_NAME=.*|MAIL_FROM_NAME=\"$MAIL_FROM_NAME\"|" /var/www/html/.env; fi
if [ -n "$STRIPE_KEY" ]; then sed -i "s|STRIPE_KEY=.*|STRIPE_KEY=$STRIPE_KEY|" /var/www/html/.env; fi
if [ -n "$STRIPE_SECRET" ]; then sed -i "s|STRIPE_SECRET=.*|STRIPE_SECRET=$STRIPE_SECRET|" /var/www/html/.env; fi
if [ -n "$STRIPE_WEBHOOK_SECRET" ]; then sed -i "s|STRIPE_WEBHOOK_SECRET=.*|STRIPE_WEBHOOK_SECRET=$STRIPE_WEBHOOK_SECRET|" /var/www/html/.env; fi
if [ -n "$SESSION_LIFETIME" ]; then sed -i "s|SESSION_LIFETIME=.*|SESSION_LIFETIME=$SESSION_LIFETIME|" /var/www/html/.env; fi
if [ -n "$SANCTUM_STATEFUL_DOMAINS" ]; then sed -i "s|SANCTUM_STATEFUL_DOMAINS=.*|SANCTUM_STATEFUL_DOMAINS=$SANCTUM_STATEFUL_DOMAINS|" /var/www/html/.env; fi

echo ".env dosyası oluşturuldu."
echo "APP_KEY oluşturuluyor..."
php artisan key:generate --force --no-interaction

