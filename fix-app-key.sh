#!/bin/sh
# Container içinde çalıştırılacak APP_KEY düzeltme scripti

cd /var/www/html

# .env dosyası var mı kontrol et
if [ ! -f .env ]; then
    echo ".env dosyası bulunamadı, oluşturuluyor..."
    touch .env
fi

# APP_KEY satırını kontrol et ve düzelt
if grep -q "^APP_KEY=" .env; then
    echo "APP_KEY satırı bulundu, güncelleniyor..."
    # Mevcut APP_KEY satırını sil
    sed -i '/^APP_KEY=/d' .env
fi

# Yeni APP_KEY oluştur
echo "APP_KEY oluşturuluyor..."
NEW_KEY=$(php artisan key:generate --show 2>/dev/null | grep "base64:" | head -1)

if [ -z "$NEW_KEY" ]; then
    # Alternatif yöntem: openssl kullan
    NEW_KEY="base64:$(openssl rand -base64 32)"
fi

# APP_KEY'i .env dosyasına ekle (APP_NAME'den sonra)
if grep -q "^APP_NAME=" .env; then
    sed -i "/^APP_NAME=/a APP_KEY=$NEW_KEY" .env
else
    echo "APP_KEY=$NEW_KEY" >> .env
fi

echo "APP_KEY başarıyla oluşturuldu: ${NEW_KEY:0:30}..."
cat .env | grep APP_KEY

