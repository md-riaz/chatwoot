# ClearLine Laravel Troubleshooting Guide

This comprehensive guide covers common issues, debugging techniques, and maintenance procedures for ClearLine Laravel in production environments.

## Table of Contents

1. [Quick Diagnostics](#quick-diagnostics)
2. [Application Issues](#application-issues)
3. [Database Issues](#database-issues)
4. [Queue and Background Jobs](#queue-and-background-jobs)
5. [WebSocket and Real-time Issues](#websocket-and-real-time-issues)
6. [Channel Integration Issues](#channel-integration-issues)
7. [Performance Issues](#performance-issues)
8. [Security Issues](#security-issues)
9. [File Storage Issues](#file-storage-issues)
10. [Email Issues](#email-issues)
11. [Third-Party Integration Issues](#third-party-integration-issues)
12. [Monitoring and Logging](#monitoring-and-logging)
13. [Maintenance Procedures](#maintenance-procedures)
14. [Emergency Procedures](#emergency-procedures)

## Quick Diagnostics

### System Health Check

```bash
# Quick system overview
php artisan about

# Check application status
php artisan health:check

# Check database connection
php artisan tinker
>>> DB::connection()->getPdo();
>>> exit

# Check Redis connection
redis-cli ping

# Check queue status
php artisan horizon:status

# Check WebSocket server
curl -H "Connection: Upgrade" -H "Upgrade: websocket" http://localhost:8080/ws
```

### Log Analysis

```bash
# Check recent application logs
tail -f storage/logs/laravel.log

# Check web server logs
tail -f /var/log/nginx/clearline_error.log
tail -f /var/log/nginx/clearline_access.log

# Check system logs
tail -f /var/log/syslog

# Check supervisor logs
tail -f /var/log/supervisor/clearline-horizon.log
tail -f /var/log/supervisor/clearline-reverb.log
```

### Service Status

```bash
# Check all services
sudo systemctl status nginx php8.2-fpm postgresql redis-server
sudo supervisorctl status

# Check process usage
htop
ps aux | grep -E "(php|nginx|postgres|redis)"
```

## Application Issues

### Issue: Application Not Loading

**Symptoms:**
- 500 Internal Server Error
- Blank white page
- Connection refused errors

**Diagnosis:**
```bash
# Check web server status
sudo systemctl status nginx

# Check PHP-FPM status
sudo systemctl status php8.2-fpm

# Check application logs
tail -f storage/logs/laravel.log

# Check web server error logs
tail -f /var/log/nginx/clearline_error.log

# Test PHP-FPM socket
sudo -u www-data php-fpm8.2 -t
```

**Solutions:**

1. **Web Server Issues:**
   ```bash
   # Restart Nginx
   sudo systemctl restart nginx
   
   # Test Nginx configuration
   sudo nginx -t
   
   # Check Nginx error logs
   tail -f /var/log/nginx/error.log
   ```

2. **PHP-FPM Issues:**
   ```bash
   # Restart PHP-FPM
   sudo systemctl restart php8.2-fpm
   
   # Check PHP-FPM configuration
   sudo php-fpm8.2 -t
   
   # Check PHP-FPM logs
   tail -f /var/log/php8.2-fpm.log
   ```

3. **Application Configuration:**
   ```bash
   # Clear and rebuild caches
   php artisan config:clear
   php artisan config:cache
   php artisan route:clear
   php artisan route:cache
   php artisan view:clear
   php artisan view:cache
   
   # Check environment file
   php artisan config:show
   ```

4. **File Permissions:**
   ```bash
   # Fix file permissions
   sudo chown -R www-data:www-data storage bootstrap/cache
   sudo chmod -R 775 storage bootstrap/cache
   ```

### Issue: Authentication Problems

**Symptoms:**
- Users cannot log in
- Token validation failures
- Session issues

**Diagnosis:**
```bash
# Check Sanctum configuration
php artisan config:show sanctum

# Test authentication
php artisan tinker
>>> $user = App\Models\User::first();
>>> $token = $user->createToken('test');
>>> echo $token->plainTextToken;
>>> exit

# Check database sessions
php artisan tinker
>>> DB::table('personal_access_tokens')->count();
>>> exit
```

**Solutions:**

1. **Sanctum Configuration:**
   ```bash
   # Publish Sanctum configuration
   php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
   
   # Run Sanctum migrations
   php artisan migrate
   
   # Clear configuration cache
   php artisan config:clear
   php artisan config:cache
   ```

2. **CORS Issues:**
   ```bash
   # Check CORS configuration
   php artisan config:show cors
   
   # Update SANCTUM_STATEFUL_DOMAINS in .env
   SANCTUM_STATEFUL_DOMAINS=your-domain.com,localhost:3000
   ```

3. **Session Configuration:**
   ```bash
   # Check session configuration
   php artisan config:show session
   
   # Clear sessions
   php artisan session:table
   php artisan migrate
   ```

### Issue: API Errors

**Symptoms:**
- 422 Validation errors
- 500 Internal server errors
- Inconsistent API responses

**Diagnosis:**
```bash
# Enable API debugging (temporarily)
# In .env: APP_DEBUG=true (NEVER in production permanently)

# Check API logs
grep -r "ERROR" storage/logs/
grep -r "CRITICAL" storage/logs/

# Test specific endpoints
curl -X GET "https://your-domain.com/api/v1/health" \
  -H "Accept: application/json"
```

**Solutions:**

1. **Validation Errors:**
   ```bash
   # Check request validation rules
   # Review controller validation logic
   # Verify request format and required fields
   ```

2. **Route Issues:**
   ```bash
   # List all routes
   php artisan route:list
   
   # Clear route cache
   php artisan route:clear
   php artisan route:cache
   ```

3. **Middleware Issues:**
   ```bash
   # Check middleware configuration
   php artisan route:list --middleware
   
   # Test without middleware (temporarily)
   # Comment out middleware in routes/api.php
   ```

## Database Issues

### Issue: Database Connection Failures

**Symptoms:**
- "Connection refused" errors
- "Too many connections" errors
- Slow database queries

**Diagnosis:**
```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();
>>> exit

# Check PostgreSQL status
sudo systemctl status postgresql

# Check database connections
sudo -u postgres psql -c "SELECT * FROM pg_stat_activity;"

# Check database logs
tail -f /var/log/postgresql/postgresql-14-main.log
```

**Solutions:**

1. **Connection Issues:**
   ```bash
   # Restart PostgreSQL
   sudo systemctl restart postgresql
   
   # Check PostgreSQL configuration
   sudo nano /etc/postgresql/14/main/postgresql.conf
   # Verify: listen_addresses, port, max_connections
   
   # Check pg_hba.conf
   sudo nano /etc/postgresql/14/main/pg_hba.conf
   ```

2. **Too Many Connections:**
   ```bash
   # Check current connections
   sudo -u postgres psql -c "SELECT count(*) FROM pg_stat_activity;"
   
   # Kill idle connections
   sudo -u postgres psql -c "
     SELECT pg_terminate_backend(pid) 
     FROM pg_stat_activity 
     WHERE state = 'idle' AND state_change < now() - interval '1 hour';
   "
   
   # Increase max_connections in postgresql.conf
   sudo nano /etc/postgresql/14/main/postgresql.conf
   # Set: max_connections = 200
   sudo systemctl restart postgresql
   ```

3. **Performance Issues:**
   ```bash
   # Enable slow query logging
   sudo nano /etc/postgresql/14/main/postgresql.conf
   # Set: log_min_duration_statement = 1000
   
   # Analyze slow queries
   tail -f /var/log/postgresql/postgresql-14-main.log | grep "slow query"
   
   # Run database optimization
   sudo -u postgres psql clearline_production -c "VACUUM ANALYZE;"
   ```

### Issue: Migration Failures

**Symptoms:**
- Migration rollback errors
- Schema inconsistencies
- Foreign key constraint violations

**Diagnosis:**
```bash
# Check migration status
php artisan migrate:status

# Check database schema
php artisan tinker
>>> Schema::getTableListing();
>>> exit

# Check for failed migrations
grep -r "ERROR" storage/logs/ | grep -i migrate
```

**Solutions:**

1. **Failed Migrations:**
   ```bash
   # Rollback specific migration
   php artisan migrate:rollback --step=1
   
   # Reset migrations (CAUTION: Data loss)
   php artisan migrate:reset
   php artisan migrate
   
   # Fresh migration (CAUTION: Complete data loss)
   php artisan migrate:fresh
   ```

2. **Foreign Key Issues:**
   ```bash
   # Disable foreign key checks temporarily
   php artisan tinker
   >>> DB::statement('SET FOREIGN_KEY_CHECKS=0;');
   >>> // Run problematic migration
   >>> DB::statement('SET FOREIGN_KEY_CHECKS=1;');
   >>> exit
   ```

3. **Schema Inconsistencies:**
   ```bash
   # Compare schemas
   php artisan schema:dump
   
   # Manual schema fixes
   php artisan make:migration fix_schema_inconsistencies
   ```

## Queue and Background Jobs

### Issue: Jobs Not Processing

**Symptoms:**
- Jobs stuck in queue
- Failed jobs accumulating
- Horizon not processing jobs

**Diagnosis:**
```bash
# Check Horizon status
php artisan horizon:status

# Check queue status
php artisan queue:work --once

# Check failed jobs
php artisan queue:failed

# Check supervisor status
sudo supervisorctl status clearline-horizon
```

**Solutions:**

1. **Horizon Issues:**
   ```bash
   # Restart Horizon
   sudo supervisorctl restart clearline-horizon
   
   # Check Horizon logs
   tail -f /var/log/supervisor/clearline-horizon.log
   
   # Terminate Horizon gracefully
   php artisan horizon:terminate
   
   # Clear failed jobs
   php artisan queue:flush
   ```

2. **Redis Issues:**
   ```bash
   # Check Redis connection
   redis-cli ping
   
   # Check Redis memory usage
   redis-cli info memory
   
   # Clear Redis queues (CAUTION: Job loss)
   redis-cli flushdb
   ```

3. **Job Configuration:**
   ```bash
   # Check queue configuration
   php artisan config:show queue
   
   # Test job dispatch
   php artisan tinker
   >>> dispatch(new App\Jobs\TestJob());
   >>> exit
   ```

### Issue: High Memory Usage in Queue Workers

**Symptoms:**
- Workers consuming excessive memory
- Out of memory errors
- Worker processes dying

**Diagnosis:**
```bash
# Monitor memory usage
htop
ps aux | grep "queue:work"

# Check worker configuration
cat config/horizon.php
```

**Solutions:**

1. **Worker Configuration:**
   ```bash
   # Adjust worker memory limits in config/horizon.php
   'memory' => 512,
   'tries' => 3,
   'timeout' => 300,
   
   # Restart workers more frequently
   'balance' => 'auto',
   'maxProcesses' => 10,
   'maxTime' => 3600,
   ```

2. **Job Optimization:**
   ```php
   // In job classes, implement proper cleanup
   public function handle()
   {
       try {
           // Job logic here
       } finally {
           // Cleanup resources
           gc_collect_cycles();
       }
   }
   ```

## WebSocket and Real-time Issues

### Issue: WebSocket Connection Failures

**Symptoms:**
- Real-time features not working
- WebSocket connection errors
- Reverb server not responding

**Diagnosis:**
```bash
# Check Reverb status
sudo supervisorctl status clearline-reverb

# Test WebSocket connection
curl -H "Connection: Upgrade" -H "Upgrade: websocket" http://localhost:8080/ws

# Check Reverb logs
tail -f /var/log/supervisor/clearline-reverb.log

# Check network connectivity
netstat -tlnp | grep :8080
```

**Solutions:**

1. **Reverb Server Issues:**
   ```bash
   # Restart Reverb
   sudo supervisorctl restart clearline-reverb
   
   # Check Reverb configuration
   php artisan config:show broadcasting
   
   # Test Reverb manually
   php artisan reverb:start --host=0.0.0.0 --port=8080
   ```

2. **Firewall Issues:**
   ```bash
   # Check firewall rules
   sudo ufw status
   
   # Allow WebSocket port
   sudo ufw allow 8080
   
   # Check iptables
   sudo iptables -L
   ```

3. **Nginx Proxy Issues:**
   ```nginx
   # Update Nginx configuration for WebSocket proxy
   location /ws {
       proxy_pass http://127.0.0.1:8080;
       proxy_http_version 1.1;
       proxy_set_header Upgrade $http_upgrade;
       proxy_set_header Connection "upgrade";
       proxy_set_header Host $host;
       proxy_cache_bypass $http_upgrade;
   }
   ```

### Issue: Broadcasting Events Not Received

**Symptoms:**
- Events dispatched but not received
- Inconsistent real-time updates
- Channel subscription issues

**Diagnosis:**
```bash
# Test event broadcasting
php artisan tinker
>>> broadcast(new App\Events\TestEvent());
>>> exit

# Check broadcasting configuration
php artisan config:show broadcasting

# Monitor Reverb logs during event dispatch
tail -f /var/log/supervisor/clearline-reverb.log
```

**Solutions:**

1. **Event Configuration:**
   ```php
   // Ensure events implement ShouldBroadcast
   class MessageCreated implements ShouldBroadcast
   {
       use Dispatchable, InteractsWithSockets, SerializesModels;
       
       public function broadcastOn()
       {
           return new PrivateChannel('conversation.' . $this->message->conversation_id);
       }
   }
   ```

2. **Channel Authorization:**
   ```php
   // In routes/channels.php
   Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
       return $user->canAccessConversation($conversationId);
   });
   ```

## Channel Integration Issues

### Issue: WhatsApp Integration Problems

**Symptoms:**
- Messages not sending/receiving
- Webhook verification failures
- Template message errors

**Diagnosis:**
```bash
# Check WhatsApp configuration
php artisan tinker
>>> $channel = App\Models\Channels\Whatsapp::first();
>>> dd($channel->provider_config);
>>> exit

# Check webhook logs
grep -r "whatsapp" storage/logs/

# Test WhatsApp API connection
curl -X GET "https://graph.facebook.com/v18.0/me" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

**Solutions:**

1. **Configuration Issues:**
   ```bash
   # Verify WhatsApp configuration
   # Check phone_number_id, access_token, verify_token
   
   # Update webhook URL in Meta Developer Console
   # Ensure webhook URL is accessible: https://your-domain.com/api/v1/webhooks/whatsapp
   ```

2. **Webhook Verification:**
   ```php
   // Ensure webhook verification is properly implemented
   public function verifyWebhook(Request $request)
   {
       $mode = $request->query('hub_mode');
       $token = $request->query('hub_verify_token');
       $challenge = $request->query('hub_challenge');
       
       if ($mode === 'subscribe' && $token === $this->verifyToken) {
           return response($challenge, 200);
       }
       
       return response('Forbidden', 403);
   }
   ```

### Issue: Email Channel Problems

**Symptoms:**
- Emails not being fetched
- IMAP connection failures
- SMTP sending issues

**Diagnosis:**
```bash
# Test IMAP connection
php artisan tinker
>>> $imap = new Webklex\PHPIMAP\Client([
>>>     'host' => 'imap.gmail.com',
>>>     'port' => 993,
>>>     'encryption' => 'ssl',
>>>     'username' => 'your-email@gmail.com',
>>>     'password' => 'your-password'
>>> ]);
>>> $imap->connect();
>>> exit

# Check email logs
grep -r "email" storage/logs/
```

**Solutions:**

1. **IMAP Configuration:**
   ```bash
   # Verify IMAP settings
   # Check host, port, encryption, credentials
   
   # Test IMAP connection manually
   telnet imap.gmail.com 993
   ```

2. **SMTP Configuration:**
   ```bash
   # Test SMTP configuration
   php artisan tinker
   >>> Mail::raw('Test email', function ($message) {
>>>     $message->to('test@example.com')->subject('Test');
>>> });
>>> exit
   ```

## Performance Issues

### Issue: Slow Response Times

**Symptoms:**
- High response times (>1s)
- Database query timeouts
- Memory exhaustion

**Diagnosis:**
```bash
# Enable query logging
# In .env: DB_LOG_QUERIES=true

# Monitor slow queries
tail -f storage/logs/laravel.log | grep -i "slow"

# Check system resources
htop
iotop
free -h

# Profile specific endpoints
# Install Laravel Telescope for debugging
php artisan telescope:install
php artisan migrate
```

**Solutions:**

1. **Database Optimization:**
   ```bash
   # Add missing indexes
   php artisan make:migration add_missing_indexes
   
   # Optimize queries
   # Use eager loading: User::with('accounts')->get()
   # Add database indexes for frequently queried columns
   
   # Analyze query performance
   sudo -u postgres psql clearline_production -c "EXPLAIN ANALYZE SELECT * FROM conversations WHERE status = 'open';"
   ```

2. **Caching:**
   ```bash
   # Enable Redis caching
   # In .env: CACHE_DRIVER=redis
   
   # Cache configuration
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   
   # Implement application-level caching
   Cache::remember('user.conversations.' . $userId, 300, function () {
       return $user->conversations()->with('contact')->get();
   });
   ```

3. **Application Optimization:**
   ```bash
   # Enable OPcache
   # In php.ini:
   opcache.enable=1
   opcache.memory_consumption=256
   opcache.max_accelerated_files=4000
   
   # Optimize Composer autoloader
   composer dump-autoload --optimize --no-dev
   ```

### Issue: High Memory Usage

**Symptoms:**
- Out of memory errors
- Slow performance
- Process crashes

**Diagnosis:**
```bash
# Monitor memory usage
free -h
ps aux --sort=-%mem | head

# Check PHP memory limits
php -i | grep memory_limit

# Monitor application memory usage
# Add memory monitoring to critical endpoints
```

**Solutions:**

1. **PHP Configuration:**
   ```bash
   # Increase PHP memory limit
   # In php.ini: memory_limit = 512M
   
   # Optimize PHP-FPM
   # In pool configuration:
   pm.max_children = 50
   pm.start_servers = 5
   pm.min_spare_servers = 5
   pm.max_spare_servers = 35
   ```

2. **Application Optimization:**
   ```php
   // Use chunking for large datasets
   User::chunk(1000, function ($users) {
       foreach ($users as $user) {
           // Process user
       }
   });
   
   // Implement proper cleanup
   unset($largeVariable);
   gc_collect_cycles();
   ```

## Security Issues

### Issue: Authentication Bypass

**Symptoms:**
- Unauthorized access to resources
- Token validation failures
- Session hijacking

**Diagnosis:**
```bash
# Check authentication middleware
php artisan route:list --middleware

# Verify token validation
php artisan tinker
>>> $token = 'suspicious_token_here';
>>> $user = Laravel\Sanctum\PersonalAccessToken::findToken($token);
>>> dd($user);
>>> exit

# Check security logs
grep -r "unauthorized\|forbidden\|authentication" storage/logs/
```

**Solutions:**

1. **Middleware Configuration:**
   ```php
   // Ensure all protected routes use auth middleware
   Route::middleware(['auth:sanctum'])->group(function () {
       Route::apiResource('conversations', ConversationController::class);
   });
   ```

2. **Token Security:**
   ```bash
   # Revoke suspicious tokens
   php artisan tinker
   >>> Laravel\Sanctum\PersonalAccessToken::where('name', 'suspicious')->delete();
   >>> exit
   
   # Implement token rotation
   # Set token expiration in config/sanctum.php
   'expiration' => 60 * 24, // 24 hours
   ```

### Issue: SQL Injection Attempts

**Symptoms:**
- Suspicious database queries in logs
- Unexpected SQL errors
- Security scanner alerts

**Diagnosis:**
```bash
# Check for SQL injection patterns in logs
grep -r "UNION\|DROP\|INSERT\|UPDATE.*WHERE.*=" storage/logs/
grep -r "sql.*injection" /var/log/nginx/

# Monitor database logs
tail -f /var/log/postgresql/postgresql-14-main.log | grep -i "error"
```

**Solutions:**

1. **Input Validation:**
   ```php
   // Use Laravel's built-in validation
   $request->validate([
       'search' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
   ]);
   
   // Use Eloquent ORM (automatically prevents SQL injection)
   User::where('email', $request->email)->first();
   ```

2. **Security Headers:**
   ```nginx
   # Add security headers in Nginx
   add_header X-Content-Type-Options nosniff;
   add_header X-Frame-Options DENY;
   add_header X-XSS-Protection "1; mode=block";
   ```

## File Storage Issues

### Issue: File Upload Failures

**Symptoms:**
- Upload timeouts
- File size limit errors
- Storage permission issues

**Diagnosis:**
```bash
# Check file upload configuration
php -i | grep -E "upload_max_filesize|post_max_size|max_execution_time"

# Check storage permissions
ls -la storage/app/
ls -la storage/logs/

# Check disk space
df -h

# Test file upload
php artisan tinker
>>> Storage::put('test.txt', 'test content');
>>> Storage::exists('test.txt');
>>> exit
```

**Solutions:**

1. **PHP Configuration:**
   ```bash
   # Increase upload limits
   # In php.ini:
   upload_max_filesize = 100M
   post_max_size = 100M
   max_execution_time = 300
   
   # In Nginx:
   client_max_body_size 100M;
   ```

2. **Storage Permissions:**
   ```bash
   # Fix storage permissions
   sudo chown -R www-data:www-data storage/
   sudo chmod -R 775 storage/
   ```

3. **Disk Space:**
   ```bash
   # Clean up old files
   find storage/logs/ -name "*.log" -mtime +30 -delete
   
   # Clean up temporary files
   php artisan storage:link
   ```

## Email Issues

### Issue: Emails Not Sending

**Symptoms:**
- Email notifications not delivered
- SMTP connection failures
- Queue jobs failing

**Diagnosis:**
```bash
# Test email configuration
php artisan tinker
>>> Mail::raw('Test email', function ($message) {
>>>     $message->to('test@example.com')->subject('Test');
>>> });
>>> exit

# Check mail logs
grep -r "mail\|smtp" storage/logs/

# Check queue for failed mail jobs
php artisan queue:failed
```

**Solutions:**

1. **SMTP Configuration:**
   ```bash
   # Verify SMTP settings in .env
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=your-email@gmail.com
   MAIL_PASSWORD=your-app-password
   MAIL_ENCRYPTION=tls
   
   # Test SMTP connection
   telnet smtp.gmail.com 587
   ```

2. **Authentication Issues:**
   ```bash
   # For Gmail, use App Passwords
   # Enable 2FA and generate App Password
   
   # For other providers, check authentication requirements
   ```

## Third-Party Integration Issues

### Issue: API Rate Limiting

**Symptoms:**
- 429 Too Many Requests errors
- Integration timeouts
- Service unavailable errors

**Diagnosis:**
```bash
# Check integration logs
grep -r "rate.limit\|429\|too.many" storage/logs/

# Monitor API usage
# Check third-party service dashboards
```

**Solutions:**

1. **Rate Limit Handling:**
   ```php
   // Implement exponential backoff
   public function handleRateLimit($exception)
   {
       $retryAfter = $exception->getResponse()->getHeader('Retry-After')[0] ?? 60;
       sleep($retryAfter);
       return $this->retry();
   }
   ```

2. **Request Optimization:**
   ```php
   // Batch API requests
   // Cache API responses
   // Implement request queuing
   ```

## Monitoring and Logging

### Log Management

```bash
# Configure log rotation
sudo nano /etc/logrotate.d/clearline

# Content:
/path/to/clearline/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    notifempty
    create 644 www-data www-data
}
```

### Health Monitoring

```bash
# Create health check script
cat > /usr/local/bin/clearline-health-check.sh << 'EOF'
#!/bin/bash

# Check application health
curl -f https://your-domain.com/health || echo "Health check failed"

# Check database
php artisan tinker --execute="DB::connection()->getPdo();" || echo "Database check failed"

# Check Redis
redis-cli ping || echo "Redis check failed"

# Check queue
php artisan horizon:status || echo "Queue check failed"
EOF

chmod +x /usr/local/bin/clearline-health-check.sh

# Add to crontab
*/5 * * * * /usr/local/bin/clearline-health-check.sh
```

## Maintenance Procedures

### Daily Maintenance

```bash
#!/bin/bash
# daily-maintenance.sh

# Check disk space
df -h | awk '$5 > 80 {print "Warning: " $0}'

# Check log file sizes
find storage/logs/ -name "*.log" -size +100M

# Check failed jobs
php artisan queue:failed | wc -l

# Check system resources
free -h
uptime
```

### Weekly Maintenance

```bash
#!/bin/bash
# weekly-maintenance.sh

# Database maintenance
php artisan tinker --execute="DB::statement('VACUUM ANALYZE;');"

# Clear old logs
find storage/logs/ -name "*.log" -mtime +7 -delete

# Update dependencies (after testing)
# composer update --no-dev

# Security updates
sudo apt update && sudo apt upgrade -y
```

### Monthly Maintenance

```bash
#!/bin/bash
# monthly-maintenance.sh

# Full database backup
pg_dump clearline_production | gzip > /backup/monthly_$(date +%Y%m%d).sql.gz

# Performance analysis
php artisan telescope:clear

# Security audit
# Run security scanning tools
# Review access logs for suspicious activity

# Capacity planning
# Review resource usage trends
# Plan for scaling if needed
```

## Emergency Procedures

### Application Down

1. **Immediate Response:**
   ```bash
   # Put application in maintenance mode
   php artisan down --message="Emergency maintenance in progress"
   
   # Check system status
   systemctl status nginx php8.2-fpm postgresql redis-server
   ```

2. **Diagnosis:**
   ```bash
   # Check recent logs
   tail -100 storage/logs/laravel.log
   tail -100 /var/log/nginx/clearline_error.log
   
   # Check system resources
   htop
   df -h
   ```

3. **Recovery:**
   ```bash
   # Restart services
   sudo systemctl restart nginx php8.2-fpm
   sudo supervisorctl restart all
   
   # Clear caches
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   
   # Bring application back online
   php artisan up
   ```

### Database Corruption

1. **Immediate Response:**
   ```bash
   # Stop application
   php artisan down
   
   # Stop database connections
   sudo systemctl stop php8.2-fpm
   ```

2. **Recovery:**
   ```bash
   # Check database integrity
   sudo -u postgres pg_dump --schema-only clearline_production > schema_check.sql
   
   # Restore from backup if needed
   sudo -u postgres psql clearline_production < /backup/latest_backup.sql
   
   # Restart services
   sudo systemctl start php8.2-fpm
   php artisan up
   ```

### Security Breach

1. **Immediate Response:**
   ```bash
   # Put application in maintenance mode
   php artisan down --message="Security maintenance in progress"
   
   # Revoke all API tokens
   php artisan tinker --execute="Laravel\Sanctum\PersonalAccessToken::truncate();"
   
   # Change application key
   php artisan key:generate --force
   ```

2. **Investigation:**
   ```bash
   # Check access logs
   grep -E "POST|PUT|DELETE" /var/log/nginx/clearline_access.log | tail -1000
   
   # Check for suspicious activity
   grep -i "hack\|inject\|exploit" /var/log/nginx/clearline_access.log
   
   # Check application logs
   grep -i "error\|exception\|unauthorized" storage/logs/laravel.log
   ```

3. **Recovery:**
   ```bash
   # Update all passwords
   # Patch security vulnerabilities
   # Review and update security configurations
   # Notify affected users
   # Bring application back online after security review
   php artisan up
   ```

## Getting Help

### Internal Resources

1. **Documentation:**
   - Check this troubleshooting guide
   - Review application logs
   - Check Laravel documentation

2. **Team Escalation:**
   - Contact development team
   - Provide error messages and logs
   - Include steps to reproduce

### External Resources

1. **Community Support:**
   - Laravel community forums
   - Stack Overflow
   - GitHub issues

2. **Professional Support:**
   - Laravel support contracts
   - Third-party consulting
   - Emergency support services

### Information to Provide

When seeking help, always include:

- Error messages (exact text)
- Log excerpts (relevant portions)
- Steps to reproduce the issue
- System information (OS, PHP version, etc.)
- Recent changes made to the system

---

**Last Updated:** 2025-01-02  
**Version:** 1.0  
**Maintainer:** Development Team