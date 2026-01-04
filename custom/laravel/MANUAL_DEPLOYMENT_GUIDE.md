# Manual Deployment Guide

This guide provides step-by-step instructions for manually deploying ClearLine Laravel application on a production server.

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Quick Start](#quick-start)
3. [Detailed Setup](#detailed-setup)
4. [CORS Configuration](#cors-configuration)
5. [Troubleshooting](#troubleshooting)

## Prerequisites

### Minimum System Requirements

- **OS**: Ubuntu 20.04+, Debian 11+, CentOS 8+, or RHEL 8+
- **PHP**: 8.2 or higher
- **Database**: PostgreSQL 14+ (16+ recommended)
- **Cache**: Redis 7+
- **Memory**: 2GB RAM minimum (4GB+ recommended)
- **Storage**: 10GB minimum

### Required PHP Extensions

- pdo
- pdo_pgsql
- pgsql
- redis
- mbstring
- xml
- curl
- zip
- bcmath
- intl
- ctype
- fileinfo
- json
- tokenizer

## Quick Start

### Automated Setup (Recommended)

```bash
# 1. Clone the repository
git clone <your-repository-url>
cd chatwoot/custom/laravel

# 2. Run environment setup script
sudo bash deploy/setup-environment.sh

# 3. Configure environment
cp deploy/.env.production.example .env
# Edit .env with your settings

# 4. Setup application
php artisan key:generate
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan db:seed
php artisan horizon:install

# 5. Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 6. Configure web server and supervisor
# Copy config files from deploy/ directory

# 7. For subsequent deployments
bash deploy/deploy.sh
```

## Detailed Setup

### Step 1: Install PHP 8.2+

#### Ubuntu/Debian

```bash
sudo apt-get update
sudo apt-get install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt-get update

sudo apt-get install -y \
    php8.2-cli \
    php8.2-fpm \
    php8.2-pgsql \
    php8.2-redis \
    php8.2-mbstring \
    php8.2-xml \
    php8.2-curl \
    php8.2-zip \
    php8.2-bcmath \
    php8.2-intl \
    php8.2-gd \
    php8.2-opcache
```

#### CentOS/RHEL

```bash
sudo yum install -y epel-release
sudo yum install -y https://rpms.remirepo.net/enterprise/remi-release-$(rpm -E %{rhel}).rpm
sudo yum module reset php -y
sudo yum module enable php:remi-8.2 -y

sudo yum install -y \
    php \
    php-cli \
    php-fpm \
    php-pgsql \
    php-redis \
    php-mbstring \
    php-xml \
    php-curl \
    php-zip \
    php-bcmath \
    php-intl \
    php-gd \
    php-opcache
```

### Step 2: Install Composer

```bash
cd /tmp
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --quiet
sudo mv composer.phar /usr/local/bin/composer
rm composer-setup.php

# Verify installation
composer --version
```

### Step 3: Install PostgreSQL

#### Ubuntu/Debian

```bash
sudo sh -c 'echo "deb http://apt.postgresql.org/pub/repos/apt $(lsb_release -cs)-pgdg main" > /etc/apt/sources.list.d/pgdg.list'
wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | sudo apt-key add -
sudo apt-get update
sudo apt-get install -y postgresql-16 postgresql-client-16

# Start and enable service
sudo systemctl start postgresql
sudo systemctl enable postgresql
```

#### CentOS/RHEL

```bash
sudo yum install -y https://download.postgresql.org/pub/repos/yum/reporpms/EL-$(rpm -E %{rhel})-x86_64/pgdg-redhat-repo-latest.noarch.rpm
sudo yum install -y postgresql16 postgresql16-server

# Initialize and start
sudo /usr/pgsql-16/bin/postgresql-16-setup initdb
sudo systemctl start postgresql-16
sudo systemctl enable postgresql-16
```

#### Create Database

```bash
sudo -u postgres psql

CREATE DATABASE clearline_production;
CREATE USER clearline WITH PASSWORD 'your_secure_password';
GRANT ALL PRIVILEGES ON DATABASE clearline_production TO clearline;
\q
```

### Step 4: Install Redis

#### Ubuntu/Debian

```bash
sudo apt-get install -y redis-server

# Start and enable service
sudo systemctl start redis-server
sudo systemctl enable redis-server

# Verify installation
redis-cli ping  # Should return PONG
```

#### CentOS/RHEL

```bash
sudo yum install -y redis

# Start and enable service
sudo systemctl start redis
sudo systemctl enable redis

# Verify installation
redis-cli ping  # Should return PONG
```

### Step 5: Install Supporting Tools

```bash
# Ubuntu/Debian
sudo apt-get install -y git curl wget unzip supervisor nginx

# CentOS/RHEL
sudo yum install -y git curl wget unzip supervisor nginx
```

### Step 6: Setup Application

```bash
# Clone repository
sudo mkdir -p /var/www
cd /var/www
sudo git clone <your-repository-url> html
sudo chown -R www-data:www-data html  # Ubuntu/Debian
# sudo chown -R nginx:nginx html      # CentOS/RHEL

# Navigate to Laravel directory
cd html/custom/laravel

# Copy environment file
cp deploy/.env.production.example .env

# Edit .env and configure:
# - APP_URL (your domain)
# - DB_* (database credentials)
# - REDIS_* (Redis settings)
# - CORS_ALLOWED_ORIGINS (your frontend URLs)
# - Mail settings
# - Reverb settings
nano .env  # or vim .env

# Generate application key
php artisan key:generate

# Install dependencies
composer install --no-dev --no-interaction --optimize-autoloader

# Setup database
php artisan migrate --force
php artisan db:seed

# Install Horizon
php artisan horizon:install

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Set proper permissions
sudo chown -R www-data:www-data storage bootstrap/cache  # Ubuntu/Debian
# sudo chown -R nginx:nginx storage bootstrap/cache      # CentOS/RHEL
sudo chmod -R 775 storage bootstrap/cache
```

### Step 7: Configure Nginx

Create `/etc/nginx/sites-available/clearline` (Ubuntu/Debian) or `/etc/nginx/conf.d/clearline.conf` (CentOS/RHEL):

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/html/custom/laravel/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;  # Ubuntu/Debian
        # fastcgi_pass 127.0.0.1:9000;                    # CentOS/RHEL
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site and restart Nginx:

```bash
# Ubuntu/Debian
sudo ln -s /etc/nginx/sites-available/clearline /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx

# CentOS/RHEL
sudo nginx -t
sudo systemctl restart nginx
```

### Step 8: Configure Supervisor

Copy supervisor configuration files:

```bash
sudo cp deploy/supervisor/*.conf /etc/supervisor/conf.d/

# Edit each file to set correct paths
sudo nano /etc/supervisor/conf.d/laravel-worker.conf
sudo nano /etc/supervisor/conf.d/laravel-horizon.conf
sudo nano /etc/supervisor/conf.d/laravel-reverb.conf

# Reload supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all

# Check status
sudo supervisorctl status
```

### Step 9: SSL Certificate (Optional but Recommended)

Install Certbot and obtain SSL certificate:

```bash
# Ubuntu/Debian
sudo apt-get install -y certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com

# CentOS/RHEL
sudo yum install -y certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com
```

## CORS Configuration

ClearLine includes built-in CORS support for API-only setups with separate frontends.

### Environment Configuration

In your `.env` file:

```bash
# Development - allow all origins
CORS_ALLOWED_ORIGINS=*

# Production - specify exact origins (comma-separated)
CORS_ALLOWED_ORIGINS=https://app.yourdomain.com,https://admin.yourdomain.com
```

### Default CORS Settings

The following settings are configured in `config/cors.php`:

- **Allowed Paths**: `api/*`, `sanctum/csrf-cookie`, `broadcasting/auth`
- **Allowed Methods**: All HTTP methods (GET, POST, PUT, DELETE, etc.)
- **Allowed Headers**: All headers
- **Credentials Support**: Enabled

### Testing CORS

Test CORS configuration:

```bash
curl -H "Origin: https://app.yourdomain.com" \
     -H "Access-Control-Request-Method: POST" \
     -H "Access-Control-Request-Headers: Content-Type" \
     -X OPTIONS \
     https://api.yourdomain.com/api/v1/accounts
```

Expected response headers:
```
Access-Control-Allow-Origin: https://app.yourdomain.com
Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE
Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With
```

## Troubleshooting

### Common Issues

#### 1. CORS Errors from Frontend

**Symptoms**: Browser console shows CORS errors like "No 'Access-Control-Allow-Origin' header"

**Solutions**:
```bash
# Verify CORS_ALLOWED_ORIGINS in .env
grep CORS_ALLOWED_ORIGINS .env

# Make sure it matches your frontend URL exactly (no trailing slash)
# Correct: https://app.yourdomain.com
# Wrong: https://app.yourdomain.com/

# Clear config cache
php artisan config:clear
php artisan config:cache

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

#### 2. Database Connection Failed

**Symptoms**: "SQLSTATE[08006] Unable to connect to database"

**Solutions**:
```bash
# Check PostgreSQL is running
sudo systemctl status postgresql

# Test connection
psql -h localhost -U clearline -d clearline_production

# Check .env database settings
grep DB_ .env

# Verify PostgreSQL allows local connections
sudo nano /etc/postgresql/16/main/pg_hba.conf
# Add: host clearline_production clearline 127.0.0.1/32 md5

# Restart PostgreSQL
sudo systemctl restart postgresql
```

#### 3. Redis Connection Failed

**Symptoms**: "Connection refused [tcp://127.0.0.1:6379]"

**Solutions**:
```bash
# Check Redis is running
sudo systemctl status redis

# Test Redis connection
redis-cli ping

# Check .env Redis settings
grep REDIS_ .env

# Restart Redis
sudo systemctl restart redis
```

#### 4. Queue Jobs Not Processing

**Symptoms**: Jobs remain in queue, not being processed

**Solutions**:
```bash
# Check Horizon status
php artisan horizon:status

# View Horizon logs
tail -f storage/logs/horizon.log

# Restart Horizon via Supervisor
sudo supervisorctl restart laravel-horizon

# Check worker status
sudo supervisorctl status
```

#### 5. WebSocket Connection Failed

**Symptoms**: Real-time features not working

**Solutions**:
```bash
# Check Reverb is running
sudo supervisorctl status laravel-reverb

# View Reverb logs
tail -f storage/logs/reverb.log

# Check firewall allows WebSocket port
sudo ufw allow 8080  # Ubuntu/Debian
# sudo firewall-cmd --add-port=8080/tcp --permanent  # CentOS/RHEL

# Restart Reverb
sudo supervisorctl restart laravel-reverb
```

#### 6. 500 Internal Server Error

**Symptoms**: White screen or 500 error

**Solutions**:
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check PHP error logs
tail -f /var/log/php8.2-fpm.log

# Check Nginx error logs
tail -f /var/log/nginx/error.log

# Verify file permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

#### 7. Permission Denied Errors

**Symptoms**: "Permission denied" when accessing files

**Solutions**:
```bash
# Set correct ownership
sudo chown -R www-data:www-data /var/www/html/custom/laravel  # Ubuntu/Debian
# sudo chown -R nginx:nginx /var/www/html/custom/laravel      # CentOS/RHEL

# Set correct permissions
sudo chmod -R 755 /var/www/html/custom/laravel
sudo chmod -R 775 /var/www/html/custom/laravel/storage
sudo chmod -R 775 /var/www/html/custom/laravel/bootstrap/cache
```

### Getting Help

If you encounter issues not covered here:

1. Check the main [README.md](../README.md) for additional information
2. Review Laravel logs: `storage/logs/laravel.log`
3. Check system logs: `/var/log/nginx/error.log`, `/var/log/php8.2-fpm.log`
4. Verify all services are running: `sudo systemctl status postgresql redis nginx php8.2-fpm`
5. Open an issue on GitHub with detailed error logs and system information

## Continuous Deployment

For subsequent deployments after initial setup:

```bash
cd /var/www/html/custom/laravel
bash deploy/deploy.sh
```

The enhanced deployment script automatically:
- ✓ Verifies all system requirements
- ✓ Checks PHP version and extensions
- ✓ Tests database and Redis connectivity
- ✓ Pulls latest code
- ✓ Installs/updates dependencies
- ✓ Runs migrations
- ✓ Clears and rebuilds caches
- ✓ Restarts queue workers and WebSocket server

## Security Recommendations

1. **Always use HTTPS** in production with valid SSL certificates
2. **Restrict CORS origins** to specific domains (never use `*` in production)
3. **Use strong database passwords** and change default credentials
4. **Configure Redis authentication** with `requirepass`
5. **Keep software updated** regularly (PHP, PostgreSQL, Redis, OS packages)
6. **Enable firewall** and restrict access to necessary ports only
7. **Regular backups** of database and application files
8. **Monitor logs** for suspicious activity

---

**Last Updated**: January 2026
**Laravel Version**: 12.x
**PHP Version**: 8.2+
