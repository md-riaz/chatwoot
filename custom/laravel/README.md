# ClearLine Laravel Project

ClearLine is a scalable, sustainable customer engagement platform built on Laravel 12. It provides real-time chat, multi-channel support, automation, analytics, and more—designed for modern businesses and extensible for enterprise needs.

## Features
- Multi-channel: Web chat, Email, WhatsApp, Facebook, Telegram, Twitter, SMS, LINE
- Real-time messaging (Laravel Reverb)
- Teams, labels, canned responses, macros
- Automation rules, SLAs, reporting, CSAT
- Help center, knowledge base, portals
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

### 1. Clone the repository
```bash
git clone https://github.com/your-org/clearline.git
cd clearline/custom/laravel
```

### 2. Install PHP dependencies
```bash
composer install
```

### 3. Configure environment
```bash
cp .env.example .env
php artisan key:generate
```
Edit `.env` to set your database, Redis, mail, and app URL settings. Key variables:
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `REDIS_HOST`, `REDIS_PORT`, `REDIS_PASSWORD`
- `APP_URL` (your public URL)

### 4. Run database migrations
```bash
php artisan migrate
```

### 5. Seed roles, permissions, and onboarding flag
```bash
php artisan db:seed
```
This does NOT create any default users or accounts for production. It only prepares roles/permissions and enables the onboarding API for secure first admin setup.

### 6. (Optional) Build frontend assets
If you use a frontend or UI, follow the relevant instructions (e.g., npm install && npm run build).

### 7. Start the application
```bash
php artisan serve
# Or use Docker Compose: docker-compose up -d
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
## First-Time Setup: Super Admin Onboarding

After initial install and seeding, you must create the first super admin and account using the onboarding API. This endpoint is available only once, immediately after seeding (the onboarding flag is set by the seeder).

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
# ClearLine

<p align="center">
  <strong>A scalable, sustainable customer engagement platform built on Laravel 12</strong>
</p>

<p align="center">
  <a href="#features">Features</a> •
  <a href="#quick-start">Quick Start</a> •
  <a href="#architecture">Architecture</a> •
  <a href="#api-documentation">API</a> •
  <a href="#deployment">Deployment</a>
</p>

---

## About ClearLine

ClearLine is a comprehensive customer engagement platform designed for modern businesses. Built on Laravel 12, it provides enterprise-grade reliability, real-time communication, and seamless integrations.

### Key Features

- **Multi-Channel Support**: Web chat, Email, WhatsApp, Facebook, Telegram, Twitter, SMS, and LINE
- **Real-Time Communication**: Laravel Reverb WebSocket for instant messaging
- **Team Collaboration**: Teams, labels, canned responses, and macros
- **Automation**: Advanced automation rules and SLA management
- **Analytics**: Comprehensive reporting and CSAT surveys
- **Help Center**: Knowledge base with portals, categories, and articles
- **Integrations**: Slack, Linear, Dialogflow, OpenAI, and more

## Quick Start

### Requirements

- PHP 8.2+
- PostgreSQL 14+
- Redis 7+
- Composer 2+

### Installation

```bash
# Clone the repository
git clone https://github.com/your-org/clearline.git
cd clearline/custom/laravel

# Install dependencies
composer install

# Configure environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Seed default data (optional)
php artisan db:seed

# Start development server
composer dev
```

### Docker Deployment

```bash
cd deploy
docker-compose up -d
```

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

ClearLine provides a comprehensive REST API covering all functionality, including voice channel (Twilio) integration:

### Core Resources
- `/api/v1/accounts` - Account management
- `/api/v1/accounts/{id}/conversations` - Conversations
- `/api/v1/accounts/{id}/contacts` - Contacts
- `/api/v1/accounts/{id}/inboxes` - Inboxes

### Voice Channel (Twilio)
- `/api/v1/webhooks/voice/call/{phone}` - Twilio webhook for incoming calls (TwiML response)
- `/api/v1/webhooks/voice/status/{phone}` - Twilio webhook for call status events
- `/api/v1/webhooks/voice/conference_status/{phone}` - Twilio webhook for conference status events

#### Example: Twilio Webhook Integration

Configure your Twilio number to use these webhook URLs for voice calls:

- Voice URL: `POST /api/v1/webhooks/voice/call/{phone}`
- Status Callback: `POST /api/v1/webhooks/voice/status/{phone}`
- Conference Status Callback: `POST /api/v1/webhooks/voice/conference_status/{phone}`

See [docs/API_DOCUMENTATION.md](./docs/API_DOCUMENTATION.md) for full details and payload examples.

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
```

### Test Statistics

- ✅ **96.15% verification pass rate**
- ✅ **100% core API coverage** (40/40 specs mapped)
- ✅ **93.1% factories use Laravel Faker** (27/29)
- ✅ **All models verified against Rails**

## Contributing

We welcome contributions! Please review the contribution guidelines before submitting pull requests.

## License

ClearLine is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
