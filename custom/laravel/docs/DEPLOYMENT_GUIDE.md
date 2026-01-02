# ClearLine Laravel Deployment Guide

This comprehensive guide covers deploying ClearLine Laravel to production environments with best practices for security, performance, and reliability.

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Environment Setup](#environment-setup)
3. [Database Configuration](#database-configuration)
4. [Application Deployment](#application-deployment)
5. [Service Configuration](#service-configuration)
6. [Security Configuration](#security-configuration)
7. [Performance Optimization](#performance-optimization)
8. [Monitoring and Logging](#monitoring-and-logging)
9. [Backup and Recovery](#backup-and-recovery)
10. [Troubleshooting](#troubleshooting)

## Prerequisites

### System Requirements

- **Operating System**: Ubuntu 20.04+ or CentOS 8+
- **PHP**: 8.2 or higher with required extensions
- **Database**: PostgreSQL 14+ (16+ recommended)
- **Cache/Queue**: Redis 7+
- **Web Server**: Nginx 1.18+ or Apache 2.4+
- **Process Manager**: Supervisor 4.0+
- **Memory**: Minimum 4GB RAM (8GB+ recommended)
- **Storage**: Minimum 20GB SSD (100GB+ recommended)

### Required PHP Extensions

```bash
# Install PHP and required extensions
sudo apt update
sudo apt install -y php8.2 php8.2-fpm php8.2-cli php8.2-common \
    php8.2-pgsql php8.2-redis php8.2-curl php8.2-json php8.2-mbstring \
    php8.2-xml php8.2-zip php8.2-gd php8.2-intl php8.2-bcmath \
    php8.2-soap php8.2-imap php8.2-ldap
```

### Database Setup

```bash
# Install PostgreSQL
sudo apt install -y postgresql postgresql-contrib

# Create database and user
sudo -u postgres psql
CREATE DATABASE clearline_production;
CREATE USER clearline WITH PASSWORD 'secure_password_here';
GRANT ALL PRIVILEGES ON DATABASE clearline_production TO clearline;
ALTER USER clearline CREATEDB;
\q
```

### Redis Setup

```bash
# Install Redis
sudo apt install -y redis-server

# Configure Redis for production
sudo nano /etc/redis/redis.conf
# Set: maxmemory 2gb
# Set: maxmemory-policy allkeys-lru
# Set: requirepass your_redis_password

sudo systemctl restart redis-server
```

## Environment Setup

### 1. Clone and Setup Application

```bash
# Clone repository
git clone https://github.com/your-org/clearline.git
cd clearline/custom/laravel

# Install dependencies
composer install --no-dev --optimize-autoloader

# Set permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 2. Environment Configuration

```bash
# Copy production environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 3. Production Environment Variables

```env
# Application
APP_NAME="ClearLine"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_KEY=base64:your-generated-key-here

# Database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=clearline_production
DB_USERNAME=clearline
DB_PASSWORD=secure_password_here

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=your_redis_password
REDIS_PORT=6379
REDIS_DB=0

# Cache
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Broadcasting (WebSocket)
BROADCAST_DRIVER=reverb
REVERB_APP_ID=clearline
REVERB_APP_KEY=your-reverb-key
REVERB_APP_SECRET=your-reverb-secret
REVERB_HOST="0.0.0.0"
REVERB_PORT=8080
REVERB_SCHEME=http

# Mail
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-smtp-username
MAIL_PASSWORD=your-smtp-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"

# File Storage
FILESYSTEM_DISK=local
# For S3: FILESYSTEM_DISK=s3
# AWS_ACCESS_KEY_ID=your-access-key
# AWS_SECRET_ACCESS_KEY=your-secret-key
# AWS_DEFAULT_REGION=us-east-1
# AWS_BUCKET=your-bucket-name

# Logging
LOG_CHANNEL=stack
LOG_STACK=single,daily
LOG_LEVEL=info

# Security
SANCTUM_STATEFUL_DOMAINS=your-domain.com
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict

# Rate Limiting
THROTTLE_REQUESTS_PER_MINUTE=60
API_RATE_LIMIT=1000

# Third-party Integrations (Optional)
OPENAI_API_KEY=your-openai-key
SLACK_CLIENT_ID=your-slack-client-id
SLACK_CLIENT_SECRET=your-slack-client-secret
LINEAR_API_KEY=your-linear-api-key
SHOPIFY_API_KEY=your-shopify-api-key
SHOPIFY_API_SECRET=your-shopify-api-secret

# Channel Configurations
WHATSAPP_CLOUD_API_ACCESS_TOKEN=your-whatsapp-token
FACEBOOK_APP_ID=your-facebook-app-id
FACEBOOK_APP_SECRET=your-facebook-app-secret
TELEGRAM_BOT_TOKEN=your-telegram-bot-token
TWITTER_API_KEY=your-twitter-api-key
TWITTER_API_SECRET=your-twitter-api-secret
TWILIO_ACCOUNT_SID=your-twilio-account-sid
TWILIO_AUTH_TOKEN=your-twilio-auth-token
```

## Database Configuration

### 1. Run Migrations

```bash
# Run database migrations
php artisan migrate --force

# Seed initial data (roles, permissions)
php artisan db:seed --force
```

### 2. Database Optimization

```bash
# Optimize database queries
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 3. Database Backup Setup

```bash
# Create backup script
sudo nano /usr/local/bin/clearline-backup.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/clearline"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="clearline_production"
DB_USER="clearline"

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
pg_dump -U $DB_USER -h localhost $DB_NAME | gzip > $BACKUP_DIR/db_backup_$DATE.sql.gz

# Application files backup
tar -czf $BACKUP_DIR/files_backup_$DATE.tar.gz /path/to/clearline/storage

# Keep only last 7 days of backups
find $BACKUP_DIR -name "*.gz" -mtime +7 -delete

echo "Backup completed: $DATE"
```

```bash
# Make executable and add to cron
sudo chmod +x /usr/local/bin/clearline-backup.sh
sudo crontab -e
# Add: 0 2 * * * /usr/local/bin/clearline-backup.sh
```

## Application Deployment

### 1. Web Server Configuration

#### Nginx Configuration

```nginx
# /etc/nginx/sites-available/clearline
server {
    listen 80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com;
    root /path/to/clearline/custom/laravel/public;
    index index.php;

    # SSL Configuration
    ssl_certificate /path/to/ssl/certificate.crt;
    ssl_certificate_key /path/to/ssl/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private must-revalidate auth;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/javascript;

    # File Upload Limits
    client_max_body_size 100M;

    # PHP-FPM Configuration
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Laravel Routes
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # WebSocket Proxy (Reverb)
    location /app {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
    }

    # Static Assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    # Security
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Logs
    access_log /var/log/nginx/clearline_access.log;
    error_log /var/log/nginx/clearline_error.log;
}
```

```bash
# Enable site and restart Nginx
sudo ln -s /etc/nginx/sites-available/clearline /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 2. PHP-FPM Configuration

```bash
# Edit PHP-FPM pool configuration
sudo nano /etc/php/8.2/fpm/pool.d/clearline.conf
```

```ini
[clearline]
user = www-data
group = www-data
listen = /var/run/php/php8.2-fpm-clearline.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 1000

php_admin_value[error_log] = /var/log/php/clearline-error.log
php_admin_flag[log_errors] = on
php_admin_value[memory_limit] = 256M
php_admin_value[max_execution_time] = 300
php_admin_value[upload_max_filesize] = 100M
php_admin_value[post_max_size] = 100M
```

```bash
# Restart PHP-FPM
sudo systemctl restart php8.2-fpm
```

## Service Configuration

### 1. Queue Workers (Horizon)

```bash
# Install Horizon assets
php artisan horizon:install
```

Create Supervisor configuration:

```bash
sudo nano /etc/supervisor/conf.d/clearline-horizon.conf
```

```ini
[program:clearline-horizon]
process_name=%(program_name)s
command=php /path/to/clearline/custom/laravel/artisan horizon
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/clearline-horizon.log
stopwaitsecs=3600
```

### 2. WebSocket Server (Reverb)

```bash
sudo nano /etc/supervisor/conf.d/clearline-reverb.conf
```

```ini
[program:clearline-reverb]
process_name=%(program_name)s
command=php /path/to/clearline/custom/laravel/artisan reverb:start --host=0.0.0.0 --port=8080
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/clearline-reverb.log
```

### 3. Scheduled Tasks

```bash
# Add Laravel scheduler to crontab
sudo crontab -e -u www-data
# Add: * * * * * cd /path/to/clearline/custom/laravel && php artisan schedule:run >> /dev/null 2>&1
```

### 4. Start Services

```bash
# Update Supervisor configuration
sudo supervisorctl reread
sudo supervisorctl update

# Start services
sudo supervisorctl start clearline-horizon
sudo supervisorctl start clearline-reverb

# Check status
sudo supervisorctl status
```

## Security Configuration

### 1. Firewall Setup

```bash
# Configure UFW firewall
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

### 2. SSL Certificate

```bash
# Install Certbot for Let's Encrypt
sudo apt install -y certbot python3-certbot-nginx

# Obtain SSL certificate
sudo certbot --nginx -d your-domain.com

# Auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

### 3. Security Headers

Already configured in Nginx configuration above. Additional security measures:

```bash
# Disable server tokens
echo "server_tokens off;" | sudo tee -a /etc/nginx/nginx.conf

# Hide PHP version
echo "expose_php = Off" | sudo tee -a /etc/php/8.2/fpm/php.ini

sudo systemctl restart nginx php8.2-fpm
```

### 4. File Permissions

```bash
# Set proper file permissions
sudo find /path/to/clearline/custom/laravel -type f -exec chmod 644 {} \;
sudo find /path/to/clearline/custom/laravel -type d -exec chmod 755 {} \;
sudo chmod -R 775 /path/to/clearline/custom/laravel/storage
sudo chmod -R 775 /path/to/clearline/custom/laravel/bootstrap/cache
sudo chmod 755 /path/to/clearline/custom/laravel/artisan
```

## Performance Optimization

### 1. Application Optimization

```bash
# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optimize Composer autoloader
composer dump-autoload --optimize --no-dev
```

### 2. Database Optimization

```sql
-- PostgreSQL optimization
-- /etc/postgresql/14/main/postgresql.conf

shared_buffers = 256MB
effective_cache_size = 1GB
maintenance_work_mem = 64MB
checkpoint_completion_target = 0.9
wal_buffers = 16MB
default_statistics_target = 100
random_page_cost = 1.1
effective_io_concurrency = 200
work_mem = 4MB
min_wal_size = 1GB
max_wal_size = 4GB
```

### 3. Redis Optimization

```bash
# /etc/redis/redis.conf
maxmemory 2gb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000
```

### 4. PHP Optimization

```bash
# /etc/php/8.2/fpm/php.ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
opcache.enable_cli=1

realpath_cache_size=4096K
realpath_cache_ttl=600
```

## Monitoring and Logging

### 1. Application Monitoring

```bash
# Install monitoring tools
composer require spatie/laravel-health

# Configure health checks
php artisan vendor:publish --provider="Spatie\Health\HealthServiceProvider"
```

### 2. Log Management

```bash
# Configure log rotation
sudo nano /etc/logrotate.d/clearline
```

```
/path/to/clearline/custom/laravel/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    notifempty
    create 644 www-data www-data
    postrotate
        sudo supervisorctl restart clearline-horizon
    endscript
}
```

### 3. System Monitoring

```bash
# Install system monitoring
sudo apt install -y htop iotop nethogs

# Monitor services
sudo systemctl status nginx php8.2-fpm postgresql redis-server
sudo supervisorctl status
```

### 4. Application Health Checks

Create health check endpoint monitoring:

```bash
# Add to crontab for health monitoring
*/5 * * * * curl -f https://your-domain.com/health || echo "Health check failed" | mail -s "ClearLine Health Alert" admin@your-domain.com
```

## Backup and Recovery

### 1. Automated Backups

The backup script created earlier handles:
- Database backups (compressed)
- Application file backups
- Automatic cleanup of old backups

### 2. Recovery Procedures

#### Database Recovery

```bash
# Restore database from backup
gunzip -c /var/backups/clearline/db_backup_YYYYMMDD_HHMMSS.sql.gz | psql -U clearline -d clearline_production
```

#### Application Recovery

```bash
# Restore application files
tar -xzf /var/backups/clearline/files_backup_YYYYMMDD_HHMMSS.tar.gz -C /

# Restore permissions
sudo chown -R www-data:www-data /path/to/clearline/custom/laravel/storage
sudo chmod -R 775 /path/to/clearline/custom/laravel/storage
```

### 3. Disaster Recovery Plan

1. **Immediate Response**
   - Assess the scope of the issue
   - Switch to maintenance mode: `php artisan down`
   - Notify stakeholders

2. **Recovery Steps**
   - Restore from latest backup
   - Verify data integrity
   - Test critical functionality
   - Switch off maintenance mode: `php artisan up`

3. **Post-Recovery**
   - Monitor system performance
   - Review logs for issues
   - Update backup procedures if needed

## Troubleshooting

### Common Issues

#### 1. Application Not Loading

```bash
# Check web server status
sudo systemctl status nginx

# Check PHP-FPM status
sudo systemctl status php8.2-fpm

# Check application logs
tail -f /path/to/clearline/custom/laravel/storage/logs/laravel.log

# Check Nginx logs
tail -f /var/log/nginx/clearline_error.log
```

#### 2. Database Connection Issues

```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check PostgreSQL status
sudo systemctl status postgresql

# Check database logs
sudo tail -f /var/log/postgresql/postgresql-14-main.log
```

#### 3. Queue Jobs Not Processing

```bash
# Check Horizon status
sudo supervisorctl status clearline-horizon

# Check Horizon logs
tail -f /var/log/supervisor/clearline-horizon.log

# Restart Horizon
sudo supervisorctl restart clearline-horizon

# Check failed jobs
php artisan horizon:failed
```

#### 4. WebSocket Connection Issues

```bash
# Check Reverb status
sudo supervisorctl status clearline-reverb

# Check Reverb logs
tail -f /var/log/supervisor/clearline-reverb.log

# Test WebSocket connection
curl -H "Connection: Upgrade" -H "Upgrade: websocket" http://your-domain.com:8080/app
```

#### 5. High Memory Usage

```bash
# Check memory usage
free -h
htop

# Check PHP processes
ps aux | grep php

# Optimize PHP-FPM pool
sudo nano /etc/php/8.2/fpm/pool.d/clearline.conf
# Adjust pm.max_children, pm.start_servers, etc.
```

#### 6. Slow Performance

```bash
# Check database performance
php artisan telescope:install  # For debugging
php artisan migrate

# Enable query logging
# In .env: DB_LOG_QUERIES=true

# Check slow queries
tail -f /var/log/postgresql/postgresql-14-main.log | grep "slow query"

# Optimize database
php artisan optimize
```

### Debug Mode

**Never enable debug mode in production!** For troubleshooting:

```bash
# Temporarily enable debug (use with caution)
php artisan down
# Set APP_DEBUG=true in .env
php artisan config:clear
# Debug the issue
# Set APP_DEBUG=false in .env
php artisan config:cache
php artisan up
```

### Log Analysis

```bash
# Monitor application logs
tail -f storage/logs/laravel.log

# Monitor web server logs
tail -f /var/log/nginx/clearline_access.log
tail -f /var/log/nginx/clearline_error.log

# Monitor system logs
tail -f /var/log/syslog

# Search for specific errors
grep -r "ERROR" storage/logs/
grep -r "CRITICAL" storage/logs/
```

### Performance Monitoring

```bash
# Monitor system resources
htop
iotop
nethogs

# Monitor database connections
sudo -u postgres psql -c "SELECT * FROM pg_stat_activity;"

# Monitor Redis
redis-cli info memory
redis-cli info stats
```

## Maintenance

### Regular Maintenance Tasks

#### Daily
- Monitor system resources
- Check application logs
- Verify backup completion

#### Weekly
- Review performance metrics
- Update security patches
- Clean up old log files

#### Monthly
- Review and optimize database
- Update dependencies (after testing)
- Review security configurations
- Test backup restoration

### Update Procedures

```bash
# 1. Backup current installation
/usr/local/bin/clearline-backup.sh

# 2. Enable maintenance mode
php artisan down

# 3. Update code
git pull origin main
composer install --no-dev --optimize-autoloader

# 4. Run migrations
php artisan migrate --force

# 5. Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Restart services
sudo supervisorctl restart clearline-horizon
sudo supervisorctl restart clearline-reverb

# 7. Test functionality
php artisan test --env=production

# 8. Disable maintenance mode
php artisan up
```

## Support and Documentation

### Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [PostgreSQL Documentation](https://www.postgresql.org/docs/)
- [Redis Documentation](https://redis.io/documentation)
- [Nginx Documentation](https://nginx.org/en/docs/)

### Getting Help

1. Check application logs first
2. Review this troubleshooting guide
3. Search Laravel community forums
4. Contact development team with:
   - Error messages
   - Log excerpts
   - Steps to reproduce
   - System information

---

**Last Updated:** 2025-01-02  
**Version:** 1.0  
**Maintainer:** Development Team