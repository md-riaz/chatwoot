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

### CORS Configuration for API-Only Setup

ClearLine includes built-in CORS support for API-only deployments with separate frontends. This allows your application to serve as a backend API while your frontend runs on a different domain.

#### Configuration

CORS settings are configured in `config/cors.php` and can be customized via environment variables:

```bash
# .env configuration
# Use * for all origins (development only) or specify comma-separated origins
# Example: http://localhost:3000,https://yourdomain.com
CORS_ALLOWED_ORIGINS=*
```

For production, always specify exact origins:

```bash
# Production example
CORS_ALLOWED_ORIGINS=https://app.yourdomain.com,https://admin.yourdomain.com
```

#### Default CORS Settings

- **Allowed Paths**: `api/*`, `sanctum/csrf-cookie`, `broadcasting/auth`
- **Allowed Methods**: All HTTP methods
- **Allowed Headers**: All headers
- **Credentials Support**: Enabled (for cookie-based authentication)

#### Testing CORS

After configuring CORS, test it with a simple request:

```bash
curl -H "Origin: http://localhost:3000" \
     -H "Access-Control-Request-Method: POST" \
     -H "Access-Control-Request-Headers: Content-Type" \
     -X OPTIONS \
     http://localhost:8000/api/v1/accounts
```

You should see CORS headers in the response:
```
Access-Control-Allow-Origin: http://localhost:3000
Access-Control-Allow-Methods: *
Access-Control-Allow-Headers: *
```

---

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

#### Automated Environment Setup (Recommended)

Use the automated setup script to install and configure all prerequisites:

```bash
# Clone the repository
git clone https://github.com/your-org/clearline.git
cd clearline/custom/laravel

# Run environment setup (requires sudo)
sudo bash deploy/setup-environment.sh
```

This script will:
- Check and install PHP 8.2+ with required extensions
- Install Composer
- Install PostgreSQL 16
- Install Redis 7
- Install Nginx and Supervisor
- Provide configuration guidance

#### Manual Setup Steps

If you prefer manual setup or the automated script doesn't support your OS:

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
- `CORS_ALLOWED_ORIGINS` (comma-separated frontend origins)

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

#### Prerequisites

Before deploying, ensure your server meets the minimum requirements:

**System Requirements:**
- Ubuntu 20.04+ / Debian 11+ / CentOS 8+ / RHEL 8+
- PHP 8.2+ with extensions: pdo, pdo_pgsql, pgsql, redis, mbstring, xml, curl, zip, bcmath, intl, ctype, fileinfo, json, tokenizer
- Composer 2+
- PostgreSQL 14+ (16+ recommended)
- Redis 7+
- Nginx or Apache
- Supervisor (for queue workers and WebSocket server)

#### Step 1: Environment Setup

Use the automated environment setup script to prepare your server:

```bash
# Run as root or with sudo
sudo bash deploy/setup-environment.sh
```

This script will:
- Verify and install PHP 8.2+ with all required extensions
- Install Composer
- Install and configure PostgreSQL 16
- Install and configure Redis 7
- Install Nginx and Supervisor
- Provide step-by-step configuration guidance

Or manually install each component if the script doesn't support your OS.

#### Step 2: Database Setup

Create the PostgreSQL database and user:

```bash
sudo -u postgres psql
CREATE DATABASE clearline_production;
CREATE USER clearline WITH PASSWORD 'your_secure_password';
GRANT ALL PRIVILEGES ON DATABASE clearline_production TO clearline;
\q
```

#### Step 3: Application Setup

```bash
# Clone repository
cd /var/www
git clone <your-repository-url> html
cd html/custom/laravel

# Copy and configure environment file
cp deploy/.env.production.example .env
# Edit .env with your settings

# Generate application key
php artisan key:generate

# Install dependencies
composer install --no-dev --optimize-autoloader

# Run migrations
php artisan migrate --force

# Seed initial data
php artisan db:seed

# Install Horizon assets
php artisan horizon:install
```

#### Step 4: Production Optimizations

```bash
# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

#### Step 5: Configure CORS for Frontend

Edit your `.env` file to specify allowed frontend origins:

```bash
# For production, specify exact origins
CORS_ALLOWED_ORIGINS=https://app.yourdomain.com,https://admin.yourdomain.com
```

#### Step 6: Continuous Deployment

For subsequent deployments, use the enhanced deployment script:

```bash
cd /var/www/html/custom/laravel
bash deploy/deploy.sh
```

The deployment script will:
- ✓ Verify PHP version (8.2+)
- ✓ Check required PHP extensions
- ✓ Verify Composer installation
- ✓ Test database connectivity
- ✓ Test Redis connectivity
- ✓ Pull latest code
- ✓ Install dependencies
- ✓ Run migrations
- ✓ Clear and rebuild caches
- ✓ Restart queue workers and WebSocket server

### Supervisor Configuration

Supervisor configuration files are provided in `deploy/supervisor/`:
- `laravel-worker.conf` - Queue workers
- `laravel-horizon.conf` - Horizon dashboard
- `laravel-reverb.conf` - WebSocket server

Copy these files to `/etc/supervisor/conf.d/` and reload supervisor:

```bash
sudo cp deploy/supervisor/*.conf /etc/supervisor/conf.d/
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
```

### Troubleshooting

**Problem: CORS errors from frontend**
- Verify `CORS_ALLOWED_ORIGINS` in `.env` includes your frontend domain
- Check that origins don't have trailing slashes
- Clear config cache: `php artisan config:clear`

**Problem: Database connection failed**
- Verify PostgreSQL is running: `sudo systemctl status postgresql`
- Check database credentials in `.env`
- Ensure PostgreSQL accepts connections from your app server

**Problem: Redis connection failed**
- Verify Redis is running: `sudo systemctl status redis`
- Check Redis configuration in `.env`
- Test connection: `redis-cli ping`

**Problem: Queue jobs not processing**
- Check Horizon status: `php artisan horizon:status`
- View Horizon logs: `tail -f storage/logs/horizon.log`
- Restart workers: `php artisan horizon:terminate`

**Problem: WebSocket not connecting**
- Check Reverb is running via Supervisor
- Verify `REVERB_HOST` and `REVERB_PORT` in `.env`
- Check firewall allows WebSocket port (default: 8080)

---

## WebSocket Configuration (Laravel Reverb)

ClearLine uses Laravel Reverb for real-time WebSocket communication, providing instant messaging, live notifications, and real-time updates.

### Development Setup

1. **Install Reverb** (if not already installed):
```bash
php artisan install:broadcasting
```

2. **Configure Environment Variables**:
```bash
# .env
BROADCAST_CONNECTION=reverb

# Reverb WebSocket Configuration
REVERB_APP_ID=123456
REVERB_APP_KEY=your-unique-app-key
REVERB_APP_SECRET=your-unique-app-secret
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http
```

3. **Generate Reverb Credentials**:
```bash
# Generate unique credentials automatically
php artisan reverb:install
```

4. **Start Reverb Server**:
```bash
# Development
php artisan reverb:start --host=127.0.0.1 --port=8080 --debug

# Background process
php artisan reverb:start --host=127.0.0.1 --port=8080 &
```

### Frontend Configuration

Update your frontend environment for the correct WebSocket configuration:

```bash
# Frontend .env (Svelte UI)
VITE_API_BASE_URL=http://127.0.0.1:8000
# Development: Direct connection to Reverb (Pusher.js adds /app/{key} automatically)
VITE_WS_URL=ws://127.0.0.1:8080
# Production: Proxied through Nginx
# VITE_WS_URL=wss://your-domain.com/ws
```

**Important**: The frontend connects to `/ws` path which Nginx proxies to Reverb's `/app/{REVERB_APP_KEY}` endpoint.

### Production Setup

#### 1. Environment Configuration
```bash
# Production .env
REVERB_APP_ID=your-production-app-id
REVERB_APP_KEY=your-production-app-key
REVERB_APP_SECRET=your-production-app-secret
REVERB_HOST=0.0.0.0
REVERB_PORT=8080
REVERB_SCHEME=https  # Use HTTPS in production
```

#### 2. Supervisor Configuration
Create `/etc/supervisor/conf.d/laravel-reverb.conf`:
```ini
[program:laravel-reverb]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/clearline/artisan reverb:start --host=0.0.0.0 --port=8080
directory=/var/www/clearline
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/clearline/storage/logs/reverb.log
stopwaitsecs=3600
```

#### 3. Nginx Configuration
The WebSocket proxy is included in the provided Nginx configuration (`deployment/nginx/clearline.conf`). Key points:

- WebSocket requests to `/ws` are proxied to Reverb's `/app/{key}` endpoint
- Upgrade headers are properly set
- SSL termination is handled by Nginx
- Frontend connects via `wss://your-domain.com/ws` (production) or direct to Reverb (development)

#### 4. Firewall Configuration
```bash
# Allow WebSocket port
sudo ufw allow 8080/tcp

# Or if using Nginx proxy (recommended)
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
```

### Testing WebSocket Connection

1. **Check Reverb Status**:
```bash
# Check if Reverb is running
ps aux | grep reverb

# Check port is listening
netstat -tlnp | grep :8080
```

2. **Test WebSocket Connection**:
```bash
# Using wscat (install: npm install -g wscat)
# Development: Direct connection to Reverb
wscat -c ws://127.0.0.1:8080/app/clearline-app-key
# Production: Proxied connection
wscat -c wss://your-domain.com/ws

# Should connect and show Pusher welcome message
```

3. **Browser Console Test**:
```javascript
// In browser console
// Development: Direct connection (Pusher.js constructs full URL)
const ws = new WebSocket('ws://127.0.0.1:8080/app/clearline-app-key');
// Production: Proxied connection
const ws = new WebSocket('wss://your-domain.com/ws');
ws.onopen = () => console.log('Connected');
ws.onmessage = (e) => console.log('Message:', e.data);
```

### Troubleshooting

**Connection Refused**:
- Verify Reverb is running: `php artisan reverb:start`
- Check port availability: `netstat -tlnp | grep :8080`
- Verify firewall allows the port

**Authentication Errors**:
- Ensure `REVERB_APP_KEY` matches between backend and frontend
- Check broadcasting auth route is accessible: `/broadcasting/auth`
- Verify user is authenticated with valid Sanctum token

**SSL/TLS Issues in Production**:
- Use `wss://` instead of `ws://` for HTTPS sites
- Ensure SSL certificates are valid
- Check Nginx WebSocket proxy configuration

**Performance Issues**:
- Monitor Reverb logs: `tail -f storage/logs/reverb.log`
- Check Redis connection for scaling
- Consider multiple Reverb instances with load balancing

### Scaling WebSocket Connections

For high-traffic applications, enable Redis scaling:

```bash
# .env
REVERB_SCALING_ENABLED=true
REVERB_SCALING_CHANNEL=reverb
```

This allows multiple Reverb instances to share connection state via Redis.

---

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
