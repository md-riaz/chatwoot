# ClearLine Deployment Checklist

Complete checklist for deploying ClearLine to production with Laravel Reverb WebSocket support.

## Pre-Deployment

### Server Requirements
- [ ] PHP 8.2+ installed
- [ ] Composer 2+ installed
- [ ] PostgreSQL 14+ running
- [ ] Redis 7+ running
- [ ] Nginx configured
- [ ] Supervisor installed
- [ ] SSL certificates ready

### Environment Setup
- [ ] `.env` file configured from `.env.example`
- [ ] `APP_KEY` generated (`php artisan key:generate`)
- [ ] Database credentials set
- [ ] Redis credentials set
- [ ] Mail configuration set
- [ ] File storage configured

## Laravel Application

### Code Deployment
- [ ] Code deployed to `/var/www/clearline`
- [ ] Correct file permissions set (`chown -R www-data:www-data`)
- [ ] Composer dependencies installed (`composer install --no-dev --optimize-autoloader`)
- [ ] Configuration cached (`php artisan config:cache`)
- [ ] Routes cached (`php artisan route:cache`)
- [ ] Views cached (`php artisan view:cache`)

### Database Setup
- [ ] Database created
- [ ] Migrations run (`php artisan migrate --force`)
- [ ] Seeders run if needed (`php artisan db:seed --force`)

### Storage & Permissions
- [ ] Storage linked (`php artisan storage:link`)
- [ ] Log directory writable
- [ ] Cache directory writable
- [ ] Session directory writable

## WebSocket (Laravel Reverb)

### Reverb Configuration
- [ ] Reverb credentials generated and set in `.env`:
  ```bash
  REVERB_APP_ID=unique-app-id
  REVERB_APP_KEY=unique-app-key
  REVERB_APP_SECRET=unique-app-secret
  REVERB_HOST=0.0.0.0
  REVERB_PORT=8080
  REVERB_SCHEME=https
  ```
- [ ] Broadcasting connection set (`BROADCAST_CONNECTION=reverb`)
- [ ] Redis scaling enabled if needed (`REVERB_SCALING_ENABLED=true`)

### Supervisor Configuration
- [ ] Reverb supervisor config created (`/etc/supervisor/conf.d/clearline-reverb.conf`)
- [ ] Supervisor reloaded (`supervisorctl reread && supervisorctl update`)
- [ ] Reverb process started (`supervisorctl start clearline-reverb:*`)
- [ ] Reverb status verified (`supervisorctl status clearline-reverb:*`)

### Firewall & Network
- [ ] WebSocket port opened (8080) or proxied through Nginx
- [ ] Nginx WebSocket proxy configured
- [ ] SSL/TLS configured for WebSocket (wss://)

## Frontend (Svelte UI)

### Build & Deploy
- [ ] Frontend built (`pnpm run build`)
- [ ] Static files copied to Laravel public directory
- [ ] Frontend environment configured:
  ```bash
  VITE_API_BASE_URL=https://your-domain.com
  VITE_WS_URL=wss://your-domain.com/app/your-reverb-app-key
  ```

## Nginx Configuration

### Basic Setup
- [ ] Nginx configuration deployed (`deployment/nginx/clearline.conf`)
- [ ] SSL certificates configured
- [ ] Domain name configured
- [ ] Configuration tested (`nginx -t`)
- [ ] Nginx reloaded (`systemctl reload nginx`)

### WebSocket Proxy
- [ ] WebSocket location block configured
- [ ] Upstream reverb server defined
- [ ] WebSocket headers properly set
- [ ] Timeout settings configured

## Queue Processing

### Horizon Setup
- [ ] Horizon supervisor config created
- [ ] Horizon process started
- [ ] Queue workers running
- [ ] Horizon dashboard accessible

### Queue Configuration
- [ ] Redis queue connection configured
- [ ] Failed job handling set up
- [ ] Queue monitoring enabled

## Security

### SSL/TLS
- [ ] SSL certificates installed and valid
- [ ] HTTPS redirect configured
- [ ] Security headers configured
- [ ] HSTS enabled

### Application Security
- [ ] Debug mode disabled (`APP_DEBUG=false`)
- [ ] Secure session configuration
- [ ] CORS properly configured
- [ ] Rate limiting enabled
- [ ] Input validation in place

### WebSocket Security
- [ ] WSS (secure WebSocket) enabled
- [ ] Channel authorization implemented
- [ ] Connection rate limiting configured
- [ ] Origin restrictions set

## Monitoring & Logging

### Application Monitoring
- [ ] Log rotation configured
- [ ] Error monitoring set up
- [ ] Performance monitoring enabled
- [ ] Health checks configured

### WebSocket Monitoring
- [ ] Reverb logs configured
- [ ] Connection monitoring set up
- [ ] Performance metrics tracked
- [ ] Alerting configured

## Testing

### Application Testing
- [ ] API endpoints tested
- [ ] Authentication working
- [ ] Database connections verified
- [ ] File uploads working

### WebSocket Testing
- [ ] WebSocket connection established
- [ ] Real-time messaging working
- [ ] Channel subscriptions working
- [ ] Broadcasting events working

### Load Testing
- [ ] Application load tested
- [ ] WebSocket connection limits tested
- [ ] Database performance verified
- [ ] Redis performance verified

## Post-Deployment

### Verification
- [ ] Application accessible at domain
- [ ] All features working
- [ ] WebSocket connections stable
- [ ] No errors in logs

### Documentation
- [ ] Deployment documented
- [ ] Credentials securely stored
- [ ] Monitoring dashboards set up
- [ ] Team access configured

### Backup & Recovery
- [ ] Database backups configured
- [ ] File backups configured
- [ ] Recovery procedures tested
- [ ] Backup monitoring enabled

## Rollback Plan

### Preparation
- [ ] Previous version tagged
- [ ] Database backup taken
- [ ] Rollback procedure documented
- [ ] Team notified of deployment

### Rollback Steps (if needed)
1. [ ] Stop new processes
2. [ ] Restore previous code version
3. [ ] Rollback database if needed
4. [ ] Restart services
5. [ ] Verify rollback successful

## Environment-Specific Configurations

### Production
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `LOG_LEVEL=error`
- [ ] Caching enabled
- [ ] Optimization enabled

### Staging
- [ ] `APP_ENV=staging`
- [ ] `APP_DEBUG=true`
- [ ] `LOG_LEVEL=debug`
- [ ] Test data seeded
- [ ] Monitoring configured

## Troubleshooting Commands

### Application
```bash
# Check application status
php artisan about

# Clear all caches
php artisan optimize:clear

# Check configuration
php artisan config:show

# View logs
tail -f storage/logs/laravel.log
```

### WebSocket
```bash
# Check Reverb status
supervisorctl status clearline-reverb:*

# Test WebSocket connection
wscat -c wss://your-domain.com/app/your-app-key

# View Reverb logs
tail -f storage/logs/reverb.log

# Check port listening
netstat -tlnp | grep :8080
```

### System
```bash
# Check system resources
htop

# Check disk space
df -h

# Check memory usage
free -h

# Check service status
systemctl status nginx
systemctl status postgresql
systemctl status redis
```

## Performance Optimization

### Application
- [ ] OPcache enabled
- [ ] Configuration cached
- [ ] Routes cached
- [ ] Views cached
- [ ] Database queries optimized

### WebSocket
- [ ] Redis scaling enabled
- [ ] Connection pooling configured
- [ ] Message queuing optimized
- [ ] Resource limits set

### Infrastructure
- [ ] CDN configured for static assets
- [ ] Database connection pooling
- [ ] Redis memory optimization
- [ ] Nginx gzip compression

## Maintenance

### Regular Tasks
- [ ] Log rotation configured
- [ ] Cache clearing scheduled
- [ ] Database maintenance scheduled
- [ ] Security updates planned

### Monitoring
- [ ] Uptime monitoring
- [ ] Performance monitoring
- [ ] Error rate monitoring
- [ ] WebSocket connection monitoring

This checklist ensures a complete and secure deployment of ClearLine with full WebSocket functionality.