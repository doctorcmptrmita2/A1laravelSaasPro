# CodexFlow SaaS Platform

Laravel tabanlı SaaS platformu ile LiteLLM proxy sistemini yönetmek, müşterilere hizmet sunmak ve tüm işlemleri (log, maliyet, kullanım) takip etmek.

## 🚀 Özellikler

- ✅ **Multi-Tenancy**: Her müşteri kendi verilerini görür
- ✅ **API Key Yönetimi**: LiteLLM entegrasyonu ile otomatik key oluşturma
- ✅ **Usage Tracking**: Gerçek zamanlı kullanım ve maliyet takibi
- ✅ **Analytics Dashboard**: Detaylı analitik ve raporlama
- ✅ **LiteLLM Proxy**: Tüm AI provider'lara tek API ile erişim
- ✅ **Subscription Management**: Plan yönetimi ve faturalama
- ✅ **Professional Landing Page**: Modern ve çekici tasarım

## 📋 Gereksinimler

- PHP 8.2+
- PostgreSQL 15+
- Redis 7+
- Node.js 18+
- Composer 2+

## 🛠️ Kurulum

### 1. Repository'yi klonlayın

```bash
git clone https://github.com/your-username/A1laravelSaasPro.git
cd A1laravelSaasPro
```

### 2. Dependencies yükleyin

```bash
composer install
npm install --legacy-peer-deps
```

### 3. Environment dosyasını oluşturun

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Environment variables'ı ayarlayın

`.env` dosyasını düzenleyin:

```env
APP_NAME="CodexFlow SaaS"
APP_ENV=local
APP_URL=http://localhost

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=codexflow_saas
DB_USERNAME=codexflow
DB_PASSWORD=your_password

LITELLM_BASE_URL=https://roo-code-orchestrator-workflow-orchestrator.lc58dd.easypanel.host/v1
LITELLM_MASTER_KEY=sk-litellm-master-key-2025-roo-code-orchestrator
```

### 5. Database migration'ları çalıştırın

```bash
php artisan migrate
```

### 6. Frontend assets'leri build edin

```bash
npm run build
```

### 7. Development server'ı başlatın

```bash
php artisan serve
```

## 🐳 Docker ile Çalıştırma

### Build

```bash
docker build -t codexflow-saas .
```

### Run

```bash
docker run -p 8000:8000 --env-file .env codexflow-saas
```

## 📦 Easypanel Deployment

Detaylı deployment planı için `EASYPANEL_DEPLOYMENT_PLAN.md` dosyasına bakın.

### Hızlı Başlangıç

1. Easypanel'de yeni proje oluştur
2. PostgreSQL servisi ekle
3. Redis servisi ekle
4. Laravel App servisi ekle (Dockerfile kullan)
5. Environment variables'ı ayarla
6. Deploy et!

## 🔧 Yapılandırma

### LiteLLM Entegrasyonu

`config/litellm.php` dosyasından LiteLLM bağlantı ayarlarını yapılandırabilirsiniz.

### Scheduled Jobs

Sync job'ları otomatik çalışır:
- **Logs Sync**: Her 5 dakika
- **Usage Sync**: Her 15 dakika
- **Costs Sync**: Her saat

Queue worker çalıştırın:
```bash
php artisan queue:work
```

Scheduled job'ları aktif edin:
```bash
php artisan schedule:work
```

## 📚 API Dokümantasyonu

### Proxy Endpoints

```
POST /api/v1/chat/completions
POST /api/v1/completions
POST /api/v1/embeddings
```

**Headers:**
```
Authorization: Bearer {API_KEY}
Content-Type: application/json
```

### Dashboard API

```
GET  /api/api-keys          # List API keys
POST /api/api-keys          # Create API key
GET  /api/api-keys/{id}     # Get API key info
DELETE /api/api-keys/{id}   # Delete API key
```

## 🗄️ Database Schema

- `tenants` - Müşteri/şirket bilgileri
- `users` - Kullanıcılar (tenant'a bağlı)
- `subscriptions` - Abonelikler
- `api_keys` - API key'ler
- `usage_logs` - Kullanım logları
- `billing_records` - Faturalama kayıtları
- `litellm_sync_logs` - Sync logları

## 🔐 Güvenlik

- API key'ler hash'lenmiş saklanır
- Multi-tenancy ile veri izolasyonu
- Rate limiting (plan bazlı)
- CSRF koruması
- SQL injection koruması

## 📝 Lisans

Bu proje özel bir projedir.

## 🤝 Destek

Sorularınız için issue açabilirsiniz.

---

**CodexFlow SaaS** - AI API Gateway & Management Platform
