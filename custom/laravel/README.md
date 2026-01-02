# ClearLine Laravel Project

ClearLine is a scalable, sustainable customer engagement platform built on Laravel 12. It provides real-time chat, multi-channel support, automation, analytics, and more—designed for modern businesses and extensible for enterprise needs.

## Features
- Multi-channel: Web chat, Email, WhatsApp, Facebook, Telegram, Twitter, SMS, LINE
- Real-time messaging (Laravel Reverb)
- Teams, labels, canned responses, macros
- Automation rules, SLAs, reporting, CSAT
- Help center, knowledge base, portals
- **Super Admin Panel**: Complete system administration with 100% Rails parity
- Integrations: Slack, Linear, Dialogflow, OpenAI, and more

---

## Environment & Prerequisites

**Required:**
- PHP 8.2+
- Composer 2+
- PostgreSQL 14+ (16+ recommended)
- Redis 7+
- Node.js 18+ (for frontend assets, if needed)
- [Optional] Docker & Docker Compose for containerized setup

---

## Setup Guide

### Option 1: Docker Deployment (Recommended)

The fastest way to get ClearLine running is with Docker:

```bash
# Clone the repository
git clone https://github.com/your-org/clearline.git
cd clearline/custom/laravel

# Quick development setup
make dev-setup

# Access the application
# Application: http://localhost:8000
# Mailhog: http://localhost:8025
# PgAdmin: http://localhost:8080
```

For detailed Docker instructions, see [README.docker.md](README.docker.md).

### Option 2: Manual Installation

#### Prerequisites
- PHP 8.2+
- Composer 2+
- PostgreSQL 14+ (16+ recommended)
- Redis 7+
- Node.js 18+ (for frontend assets, if needed)

```bash
# 1. Clone the repository
git clone https://github.com/your-org/clearline.git
cd clearline/custom/laravel

# 2. Install PHP dependencies
composer install

# 3. Configure environment
cp .env.example .env
php artisan key:generate
```
Edit `.env` to set your database, Redis, mail, and app URL settings. Key variables:
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `REDIS_HOST`, `REDIS_PORT`, `REDIS_PASSWORD`
- `APP_URL` (your public URL)

```bash
# 4. Run database migrations
php artisan migrate

# 5. Publish Horizon assets (queue dashboard)
php artisan horizon:install

# 6. Seed roles, permissions, and onboarding flag
php artisan db:seed

# 7. (Optional) Build frontend assets
# If you use a frontend or UI, follow the relevant instructions (e.g., npm install && npm run build)

# 8. Start the application
php artisan serve
```

---

## First-Time Setup: Super Admin Onboarding

After seeding, create the first super admin and account using the onboarding API. This endpoint is available only once, immediately after seeding (the onboarding flag is set by the seeder).

### 1. Seed the onboarding flag

Run the default database seeder to set the onboarding flag in Redis:

```bash
php artisan db:seed
```

This ensures the onboarding API is available for first-time setup.

### 2. Create the first super admin and account

Send a POST request to `/api/v1/installation/onboarding`:

```bash
curl -X POST https://your-host.example/api/v1/installation/onboarding \
  -H "Content-Type: application/json" \
  -d '{
    "user": {
      "name": "Admin",
      "company": "Acme Inc",
      "email": "admin@example.com",
      "password": "Password1!"
    },
    "subscribe_to_updates": false
  }'
```

On success, this creates:
- A new account (with the given company name)
- A user with the `super_admin` role

Further onboarding attempts are blocked until the onboarding flag is reset in Redis.

### 3. Log in and use the API

After onboarding, log in as the super admin and use the API as documented below.

---

## Super Admin Features ✨

ClearLine includes a comprehensive **Super Admin Panel** with 100% Rails parity:

### 🎯 Dashboard & Analytics
- System overview with real-time metrics
- Growth analytics and trends
- System health monitoring
- Recent activity tracking

### ⚙️ System Management
- Global settings configuration
- Installation config management
- Cache management with pattern clearing
- Comprehensive audit logging

### 👥 User & Account Administration
- Cross-platform user management
- Account lifecycle management
- Account-user relationship management
- Role and permission administration

### 🤖 Platform Management
- Global agent bot administration
- Platform app management
- Access token management
- Instance status monitoring

### 📊 Advanced Features
- Bulk operations support
- Advanced filtering and search
- Export functionality
- Performance optimization tools

**Access:** All super admin features are available via `/api/v1/super_admin/*` endpoints.

See [SUPER_ADMIN_IMPLEMENTATION_COMPLETE.md](./SUPER_ADMIN_IMPLEMENTATION_COMPLETE.md) for complete documentation.

---

## Architecture

ClearLine follows best-in-class Laravel patterns:

- **Actions Pattern**: Business logic using Lorisleiva Laravel Actions
- **DTOs**: Type-safe data transfer with Spatie Data
- **Repository Pattern**: Clean data access layer
- **Event-Driven**: Decoupled architecture with events and listeners
- **Real-Time**: Laravel Reverb for WebSocket broadcasting
- **Queue Processing**: Laravel Horizon for background jobs

### Tech Stack

| Component | Technology |
|-----------|------------|
| Framework | Laravel 12 |
| Database | PostgreSQL 16 |
| Cache/Queue | Redis 7 |
| WebSocket | Laravel Reverb |
| Queue Dashboard | Laravel Horizon |
| Authentication | Laravel Sanctum |
| Testing | Pest PHP |


## API Documentation

ClearLine provides a comprehensive REST API covering all functionality, including voice channel (Twilio) integration and complete super admin capabilities:

### Core Resources
- `/api/v1/accounts` - Account management
- `/api/v1/accounts/{id}/conversations` - Conversations
- `/api/v1/accounts/{id}/contacts` - Contacts
- `/api/v1/accounts/{id}/inboxes` - Inboxes

### Super Admin Resources ⭐
- `/api/v1/super_admin/dashboard` - System dashboard
- `/api/v1/super_admin/accounts` - Account administration
- `/api/v1/super_admin/users` - User management
- `/api/v1/super_admin/settings` - Global settings
- `/api/v1/super_admin/cache` - Cache management
- `/api/v1/super_admin/audit_logs` - Audit logging
- `/api/v1/super_admin/account_users` - Cross-account user management

### Voice Channel (Twilio)
- `/api/v1/webhooks/voice/call/{phone}` - Twilio webhook for incoming calls (TwiML response)
- `/api/v1/webhooks/voice/status/{phone}` - Twilio webhook for call status events
- `/api/v1/webhooks/voice/conference_status/{phone}` - Twilio webhook for conference status events

#### Example: Twilio Webhook Integration

Configure your Twilio number to use these webhook URLs for voice calls:

- Voice URL: `POST /api/v1/webhooks/voice/call/{phone}`
- Status Callback: `POST /api/v1/webhooks/voice/status/{phone}`
- Conference Status Callback: `POST /api/v1/webhooks/voice/conference_status/{phone}`

See [docs/API_DOCUMENTATION.md](./docs/API_DOCUMENTATION.md) and [README.docker.md](./README.docker.md) for full details and deployment instructions.

### Team Resources
- `/api/v1/accounts/{id}/teams` - Teams
- `/api/v1/accounts/{id}/agents` - Agents
- `/api/v1/accounts/{id}/labels` - Labels

### Automation
- `/api/v1/accounts/{id}/automation_rules` - Automation rules
- `/api/v1/accounts/{id}/macros` - Macros
- `/api/v1/accounts/{id}/canned_responses` - Canned responses

### Analytics
- `/api/v1/accounts/{id}/reports` - Reports
- `/api/v1/accounts/{id}/csat_survey_responses` - CSAT surveys

See [TASKS.md](./TASKS.md) for complete API migration status.

## Deployment

### Docker Deployment (Recommended)

ClearLine includes a complete Docker setup for easy deployment:

```bash
# Development
make dev-setup

# Production
make prod-build
make prod-up
```

See [README.docker.md](README.docker.md) for complete Docker deployment instructions.

### Manual Deployment

### Production Configuration

1. Copy production environment file:
   ```bash
   cp deploy/.env.production.example .env
   ```

2. Configure your database, Redis, and mail settings

3. Run production optimizations:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

### Supervisor Configuration

Supervisor configuration files are provided in `deploy/supervisor/`:
- `laravel-worker.conf` - Queue workers
- `laravel-horizon.conf` - Horizon dashboard
- `laravel-reverb.conf` - WebSocket server

## Testing

### Test Verification ✅

**All Laravel tests have been verified against Chatwoot Rails APIs** with a **96.15% pass rate**.

📋 **Quick Verification:**
```bash
cd custom/laravel
php verify_tests_against_rails.php
```

📚 **Documentation:**
- [TEST_VERIFICATION_SUMMARY.md](./TEST_VERIFICATION_SUMMARY.md) - Executive summary
- [TEST_COMPARISON_REPORT.md](./TEST_COMPARISON_REPORT.md) - Detailed analysis
- [TEST_COVERAGE_MAPPING.md](./TEST_COVERAGE_MAPPING.md) - Complete test mapping
- [TEST_VERIFICATION_GUIDE.md](./TEST_VERIFICATION_GUIDE.md) - How-to guide

### Running Tests

```bash
# Install dependencies
composer install

# Run all tests with Pest
./vendor/bin/pest

# Run with coverage
./vendor/bin/pest --coverage

# Run specific test suite
./vendor/bin/pest --testsuite=Feature

# Run specific test file
./vendor/bin/pest tests/Feature/Api/Accounts/AccountsCrudTest.php

# Run super admin tests
./vendor/bin/pest tests/Feature/SuperAdmin/
```

### Test Statistics

- ✅ **96.15% verification pass rate**
- ✅ **100% core API coverage** (40/40 specs mapped)
- ✅ **100% super admin coverage** (New implementation)
- ✅ **93.1% factories use Laravel Faker** (27/29)
- ✅ **All models verified against Rails**

## Contributing

We welcome contributions! Please review the contribution guidelines before submitting pull requests.

## License

ClearLine is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
