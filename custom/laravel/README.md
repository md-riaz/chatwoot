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

ClearLine provides a comprehensive REST API covering all functionality:

### Core Resources
- `/api/v1/accounts` - Account management
- `/api/v1/accounts/{id}/conversations` - Conversations
- `/api/v1/accounts/{id}/contacts` - Contacts
- `/api/v1/accounts/{id}/inboxes` - Inboxes

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
