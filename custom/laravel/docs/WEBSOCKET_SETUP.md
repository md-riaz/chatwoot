# WebSocket Setup Guide - Laravel Reverb

This guide covers the complete setup and configuration of Laravel Reverb for real-time WebSocket communication in ClearLine.

## Table of Contents

1. [Overview](#overview)
2. [Development Setup](#development-setup)
3. [Production Deployment](#production-deployment)
4. [Frontend Integration](#frontend-integration)
5. [Nginx Configuration](#nginx-configuration)
6. [Troubleshooting](#troubleshooting)
7. [Performance Optimization](#performance-optimization)

## Overview

ClearLine uses Laravel Reverb as its WebSocket server, providing:
- Real-time messaging
- Live notifications
- Presence channels (who's online)
- Broadcasting events
- Pusher-compatible API

### Architecture

```
Frontend (Svelte) → WebSocket → Laravel Reverb → Laravel App → Database
                                      ↓
                                   Redis (scaling)
```

## Development Setup

### 1. Install and Configure Reverb

```bash
# Install Reverb (if not already installed)
composer require laravel/reverb

# Publish configuration
php artisan vendor:publish --provider="Laravel\Reverb\ReverbServiceProvider"

# Install broadcasting
php artisan install:broadcasting
```

### 2. Environment Configuration

Add to your `.env` file:

```bash
# Broadcasting
BROADCAST_CONNECTION=reverb

# Reverb Configuration
REVERB_APP_ID=123456
REVERB_APP_KEY=clearline-dev-key
REVERB_APP_SECRET=clearline-dev-secret
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http

# Optional: Enable scaling with Redis
REVERB_SCALING_ENABLED=false
REVERB_SCALING_CHANNEL=reverb
```

### 3. Generate Unique Credentials

```bash
# Generate secure random credentials
php artisan reverb:install

# Or manually generate
REVERB_APP_ID=$(openssl rand -hex 6)
REVERB_APP_KEY=$(openssl rand -base64 32 | tr -d "=+/" | cut -c1-25)
REVERB_APP_SECRET=$(openssl rand -base64 32 | tr -d "=+/" | cut -c1-25)
```

### 4. Start Development Server

```bash
# Start Reverb server
php artisan reverb:start --host=127.0.0.1 --port=8080 --debug

# In separate terminal, start Laravel
php artisan serve --host=127.0.0.1 --port=8000
```

### 5. Verify Setup

```bash
# Check Reverb is running
curl -I http://127.0.0.1:8080

# Test WebSocket connection
wscat -c ws://127.0.0.1:8080/app/clearline-dev-key
```

## Production Deployment

### 1. Environment Configuration

```bash
# Production .env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=prod-app-id-123456
REVERB_APP_KEY=prod-secure-app-key-here
REVERB_APP_SECRET=prod-secure-app-secret-here
REVERB_HOST=0.0.0.0
REVERB_PORT=8080
REVERB_SCHEME=https

# Enable Redis scaling for multiple instances
REVERB_SCALING_ENABLED=true
REVERB_SCALING_CHANNEL=reverb-prod

# Redis configuration
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=your-redis-password
```

### 2. Supervisor Configuration

Create `/etc/supervisor/conf.d/clearline-reverb.conf`:

```ini
[program:clearline-reverb]
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
stdout_logfile_maxbytes=50MB
stdout_logfile_backups=10
stopwaitsecs=3600
```

### 3. Start and Enable Supervisor

```bash
# Reload supervisor configuration
sudo supervisorctl reread
sudo supervisorctl update

# Start Reverb
sudo supervisorctl start clearline-reverb:*

# Check status
sudo supervisorctl status clearline-reverb:*
```

### 4. SSL/TLS Configuration

For production, ensure SSL is properly configured:

```bash
# .env
REVERB_SCHEME=https
APP_URL=https://your-domain.com

# Frontend should use wss://
VITE_WS_URL=wss://your-domain.com/app/prod-secure-app-key-here
```

## Frontend Integration

### 1. Svelte UI Configuration

Update `custom/ui/svelte-ui/.env`:

```bash
# Frontend .env
VITE_API_BASE_URL=https://your-domain.com

# WebSocket URL - Development vs Production
# Development: Direct connection to Reverb
VITE_WS_URL=ws://127.0.0.1:8080/app/your-reverb-app-key
# Production: Proxied through Nginx /ws path
# VITE_WS_URL=wss://your-domain.com/ws
```

### 2. WebSocket Client Usage

The Reverb client is already integrated. Example usage:

```typescript
import { getReverbClient } from '$lib/websocket/reverb-client';

// Initialize client
const client = getReverbClient({
  host: '127.0.0.1',
  port: 8080,
  key: 'clearline-dev-key',
  forceTLS: false,
  authEndpoint: 'http://127.0.0.1:8000/api/broadcasting/auth',
  auth: {
    headers: {
      Authorization: `Bearer ${token}`,
    },
  },
});

// Connect
client.connect();

// Subscribe to channels
client.subscribe('conversations', 'message.created', (data) => {
  console.log('New message:', data);
});

// Subscribe to private channels
client.subscribePrivate('user.123', 'notification', (data) => {
  console.log('Private notification:', data);
});
```

## Nginx Configuration

### 1. WebSocket Proxy Setup

The provided Nginx configuration (`deployment/nginx/clearline.conf`) includes WebSocket support with `/ws` path proxy:

```nginx
# WebSocket proxy for Laravel Reverb
location /ws {
    # Rewrite /ws to /app/{key} for Reverb compatibility
    rewrite ^/ws(.*)$ /app/$reverb_app_key$1 break;
    
    proxy_pass http://reverb;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_cache_bypass $http_upgrade;
    
    # WebSocket timeout settings
    proxy_read_timeout 86400;
    proxy_send_timeout 86400;
}

# Map to get Reverb app key from environment
map $host $reverb_app_key {
    default "clearline-app-key";
    # Add specific mappings if needed:
    # clearline.example.com "production-app-key";
}

# Upstream for Reverb
upstream reverb {
    server 127.0.0.1:8080;
}
```

### 2. Load Balancing Multiple Reverb Instances

For high availability, run multiple Reverb instances:

```nginx
upstream reverb {
    server 127.0.0.1:8080;
    server 127.0.0.1:8081;
    server 127.0.0.1:8082;
}
```

Supervisor configuration for multiple instances:

```ini
[program:clearline-reverb]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/clearline/artisan reverb:start --host=0.0.0.0 --port=808%(process_num)01d
directory=/var/www/clearline
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=3
redirect_stderr=true
stdout_logfile=/var/www/clearline/storage/logs/reverb-%(process_num)01d.log
```

## Troubleshooting

### Common Issues

#### 1. Connection Refused

**Symptoms**: WebSocket connection fails immediately

**Solutions**:
```bash
# Check if Reverb is running
ps aux | grep reverb

# Check port is listening
netstat -tlnp | grep :8080

# Check firewall
sudo ufw status
sudo ufw allow 8080/tcp

# Restart Reverb
sudo supervisorctl restart clearline-reverb:*
```

#### 2. Authentication Errors

**Symptoms**: WebSocket connects but channel subscription fails

**Solutions**:
```bash
# Verify broadcasting auth route
curl -X POST http://127.0.0.1:8000/api/broadcasting/auth \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"socket_id":"123.456","channel_name":"private-user.1"}'

# Check Sanctum configuration
php artisan config:show sanctum

# Verify CORS settings
php artisan config:show cors
```

#### 3. SSL/TLS Issues

**Symptoms**: WebSocket works on HTTP but fails on HTTPS

**Solutions**:
```bash
# Verify SSL certificates
openssl s_client -connect your-domain.com:443

# Check Nginx SSL configuration
nginx -t

# Verify WebSocket URL uses wss://
echo $VITE_WS_URL
```

#### 4. Performance Issues

**Symptoms**: Slow connections, high memory usage

**Solutions**:
```bash
# Monitor Reverb performance
tail -f storage/logs/reverb.log

# Check Redis connection
redis-cli ping

# Monitor system resources
htop

# Enable Redis scaling
# .env: REVERB_SCALING_ENABLED=true
```

### Debugging Commands

```bash
# Check Reverb configuration
php artisan config:show reverb

# Test broadcasting
php artisan tinker
>>> broadcast(new App\Events\TestEvent('Hello World'));

# Monitor WebSocket connections
sudo netstat -tulpn | grep :8080

# Check Supervisor logs
sudo tail -f /var/log/supervisor/supervisord.log

# View Reverb logs
tail -f storage/logs/reverb.log
```

## Performance Optimization

### 1. Redis Scaling

Enable Redis scaling for multiple Reverb instances:

```bash
# .env
REVERB_SCALING_ENABLED=true
REVERB_SCALING_CHANNEL=reverb-cluster

# Redis configuration
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=secure-password
REDIS_DB=0
```

### 2. Connection Limits

Configure connection limits in `config/reverb.php`:

```php
'apps' => [
    'apps' => [
        [
            'key' => env('REVERB_APP_KEY'),
            'secret' => env('REVERB_APP_SECRET'),
            'app_id' => env('REVERB_APP_ID'),
            'options' => [
                'host' => env('REVERB_HOST'),
                'port' => env('REVERB_PORT', 443),
                'scheme' => env('REVERB_SCHEME', 'https'),
                'useTLS' => env('REVERB_SCHEME', 'https') === 'https',
            ],
            'allowed_origins' => ['*'],
            'ping_interval' => env('REVERB_APP_PING_INTERVAL', 60),
            'activity_timeout' => env('REVERB_APP_ACTIVITY_TIMEOUT', 30),
            'max_connections' => env('REVERB_APP_MAX_CONNECTIONS', 1000),
            'max_message_size' => env('REVERB_APP_MAX_MESSAGE_SIZE', 10_000),
        ],
    ],
],
```

### 3. Monitoring

Set up monitoring for WebSocket health:

```bash
# Create monitoring script
cat > /usr/local/bin/check-reverb.sh << 'EOF'
#!/bin/bash
if ! curl -f -s http://127.0.0.1:8080 > /dev/null; then
    echo "Reverb is down, restarting..."
    supervisorctl restart clearline-reverb:*
fi
EOF

chmod +x /usr/local/bin/check-reverb.sh

# Add to crontab
echo "*/5 * * * * /usr/local/bin/check-reverb.sh" | crontab -
```

### 4. Resource Limits

Configure system limits for WebSocket connections:

```bash
# /etc/security/limits.conf
www-data soft nofile 65536
www-data hard nofile 65536

# /etc/systemd/system.conf
DefaultLimitNOFILE=65536
```

## Security Considerations

### 1. Authentication

- Always use HTTPS/WSS in production
- Implement proper channel authorization
- Use strong, unique app keys and secrets
- Rotate credentials regularly

### 2. Rate Limiting

Configure rate limiting in `config/reverb.php`:

```php
'rate_limiting' => [
    'enabled' => true,
    'max_connections_per_ip' => 100,
    'max_messages_per_minute' => 1000,
],
```

### 3. CORS Configuration

Restrict WebSocket origins in production:

```php
// config/reverb.php
'allowed_origins' => [
    'https://your-domain.com',
    'https://app.your-domain.com',
],
```

This completes the comprehensive WebSocket setup guide for ClearLine with Laravel Reverb.