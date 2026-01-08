# ClearLine Laravel Maintenance Guide

This comprehensive guide covers routine maintenance procedures, monitoring, performance optimization, and operational best practices for ClearLine Laravel in production environments.

## Table of Contents

1. [Maintenance Overview](#maintenance-overview)
2. [Daily Maintenance Tasks](#daily-maintenance-tasks)
3. [Weekly Maintenance Tasks](#weekly-maintenance-tasks)
4. [Monthly Maintenance Tasks](#monthly-maintenance-tasks)
5. [Quarterly Maintenance Tasks](#quarterly-maintenance-tasks)
6. [Performance Monitoring](#performance-monitoring)
7. [Database Maintenance](#database-maintenance)
8. [Security Maintenance](#security-maintenance)
9. [Backup and Recovery](#backup-and-recovery)
10. [Update Procedures](#update-procedures)
11. [Capacity Planning](#capacity-planning)
12. [Incident Response](#incident-response)

## Maintenance Overview

### Maintenance Philosophy

ClearLine Laravel maintenance follows a proactive approach:

- **Preventive**: Regular maintenance to prevent issues
- **Predictive**: Monitoring trends to anticipate problems
- **Reactive**: Quick response to incidents when they occur
- **Continuous**: Ongoing optimization and improvement

### Maintenance Windows

**Recommended Maintenance Windows:**
- **Daily**: Automated tasks during low-traffic hours (2-4 AM local time)
- **Weekly**: Manual maintenance during weekend low-traffic periods
- **Monthly**: Extended maintenance window for major updates
- **Emergency**: Immediate response for critical issues

### Maintenance Team Roles

| Role | Responsibilities |
|------|------------------|
| **System Administrator** | Infrastructure, security, backups |
| **Database Administrator** | Database optimization, backups, performance |
| **Application Developer** | Code updates, bug fixes, feature deployment |
| **DevOps Engineer** | CI/CD, monitoring, automation |
| **Security Specialist** | Security updates, vulnerability assessment |

## Daily Maintenance Tasks

### Automated Daily Tasks

Create automated scripts for daily maintenance:

```bash
#!/bin/bash
# /usr/local/bin/clearline-daily-maintenance.sh

LOG_FILE="/var/log/clearline/daily-maintenance.log"
DATE=$(date '+%Y-%m-%d %H:%M:%S')

echo "[$DATE] Starting daily maintenance..." >> $LOG_FILE

# 1. System Health Check
echo "[$DATE] Checking system health..." >> $LOG_FILE
systemctl is-active --quiet nginx || echo "[$DATE] WARNING: Nginx is not running" >> $LOG_FILE
systemctl is-active --quiet php8.2-fpm || echo "[$DATE] WARNING: PHP-FPM is not running" >> $LOG_FILE
systemctl is-active --quiet postgresql || echo "[$DATE] WARNING: PostgreSQL is not running" >> $LOG_FILE
systemctl is-active --quiet redis-server || echo "[$DATE] WARNING: Redis is not running" >> $LOG_FILE

# 2. Check Disk Space
echo "[$DATE] Checking disk space..." >> $LOG_FILE
df -h | awk '$5 > 80 {print "[$DATE] WARNING: " $0 " is over 80% full"}' >> $LOG_FILE

# 3. Check Application Health
echo "[$DATE] Checking application health..." >> $LOG_FILE
cd /path/to/clearline/custom/laravel
php artisan health:check >> $LOG_FILE 2>&1

# 4. Check Queue Status
echo "[$DATE] Checking queue status..." >> $LOG_FILE
php artisan horizon:status >> $LOG_FILE 2>&1

# 5. Check Failed Jobs
FAILED_JOBS=$(php artisan queue:failed --format=json | jq length)
if [ "$FAILED_JOBS" -gt 10 ]; then
    echo "[$DATE] WARNING: $FAILED_JOBS failed jobs in queue" >> $LOG_FILE
fi

# 6. Check Log File Sizes
echo "[$DATE] Checking log file sizes..." >> $LOG_FILE
find storage/logs/ -name "*.log" -size +100M -exec echo "[$DATE] WARNING: {} is over 100MB" \; >> $LOG_FILE

# 7. Database Connection Test
echo "[$DATE] Testing database connection..." >> $LOG_FILE
php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database OK';" >> $LOG_FILE 2>&1

# 8. Redis Connection Test
echo "[$DATE] Testing Redis connection..." >> $LOG_FILE
redis-cli ping >> $LOG_FILE 2>&1

# 9. WebSocket Server Check
echo "[$DATE] Checking WebSocket server..." >> $LOG_FILE
curl -s -H "Connection: Upgrade" -H "Upgrade: websocket" http://localhost:8080/ws > /dev/null
if [ $? -eq 0 ]; then
    echo "[$DATE] WebSocket server OK" >> $LOG_FILE
else
    echo "[$DATE] WARNING: WebSocket server not responding" >> $LOG_FILE
fi

# 10. Clean Temporary Files
echo "[$DATE] Cleaning temporary files..." >> $LOG_FILE
find /tmp -name "php*" -mtime +1 -delete
find storage/framework/cache -name "*.php" -mtime +7 -delete

echo "[$DATE] Daily maintenance completed." >> $LOG_FILE
```

### Daily Checklist

**Manual Daily Tasks:**

- [ ] Review system health dashboard
- [ ] Check error logs for new issues
- [ ] Monitor response times and performance metrics
- [ ] Verify backup completion
- [ ] Review security alerts
- [ ] Check queue processing status
- [ ] Monitor resource usage (CPU, memory, disk)

### Daily Monitoring Commands

```bash
# Quick system overview
htop

# Check service status
sudo systemctl status nginx php8.2-fpm postgresql redis-server

# Check recent errors
tail -100 storage/logs/laravel.log | grep -i error

# Check disk usage
df -h

# Check memory usage
free -h

# Check active connections
ss -tuln

# Check queue status
php artisan horizon:status
php artisan queue:failed | wc -l
```

## Weekly Maintenance Tasks

### Automated Weekly Tasks

```bash
#!/bin/bash
# /usr/local/bin/clearline-weekly-maintenance.sh

LOG_FILE="/var/log/clearline/weekly-maintenance.log"
DATE=$(date '+%Y-%m-%d %H:%M:%S')

echo "[$DATE] Starting weekly maintenance..." >> $LOG_FILE

# 1. Database Maintenance
echo "[$DATE] Running database maintenance..." >> $LOG_FILE
cd /path/to/clearline/custom/laravel
php artisan tinker --execute="DB::statement('VACUUM ANALYZE;');" >> $LOG_FILE 2>&1

# 2. Clear Old Logs
echo "[$DATE] Cleaning old log files..." >> $LOG_FILE
find storage/logs/ -name "*.log" -mtime +7 -delete
find /var/log/nginx/ -name "*.log.*" -mtime +14 -delete

# 3. Clear Application Caches
echo "[$DATE] Clearing application caches..." >> $LOG_FILE
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# 4. Rebuild Caches
echo "[$DATE] Rebuilding caches..." >> $LOG_FILE
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Update Composer Dependencies (if needed)
echo "[$DATE] Checking for dependency updates..." >> $LOG_FILE
composer outdated >> $LOG_FILE 2>&1

# 6. Run Security Scan
echo "[$DATE] Running security scan..." >> $LOG_FILE
composer audit >> $LOG_FILE 2>&1

# 7. Generate Performance Report
echo "[$DATE] Generating performance report..." >> $LOG_FILE
php artisan performance:report >> $LOG_FILE 2>&1

# 8. Clean Failed Jobs (older than 7 days)
echo "[$DATE] Cleaning old failed jobs..." >> $LOG_FILE
php artisan queue:prune-failed --hours=168

echo "[$DATE] Weekly maintenance completed." >> $LOG_FILE
```

### Weekly Checklist

**Manual Weekly Tasks:**

- [ ] Review performance metrics and trends
- [ ] Analyze slow query logs
- [ ] Check for security updates
- [ ] Review error patterns and fix recurring issues
- [ ] Update documentation if needed
- [ ] Test backup restoration procedure
- [ ] Review and optimize database indexes
- [ ] Check SSL certificate expiration
- [ ] Review user feedback and support tickets
- [ ] Plan upcoming feature deployments

### Weekly Performance Analysis

```bash
# Database performance analysis
sudo -u postgres psql clearline_production -c "
SELECT query, calls, total_time, mean_time, rows
FROM pg_stat_statements
ORDER BY total_time DESC
LIMIT 10;
"

# Check slow queries
grep "slow query" /var/log/postgresql/postgresql-14-main.log | tail -20

# Analyze web server logs
awk '{print $7}' /var/log/nginx/clearline_access.log | sort | uniq -c | sort -nr | head -20

# Check response times
awk '{print $10}' /var/log/nginx/clearline_access.log | sort -n | tail -100
```

## Monthly Maintenance Tasks

### Automated Monthly Tasks

```bash
#!/bin/bash
# /usr/local/bin/clearline-monthly-maintenance.sh

LOG_FILE="/var/log/clearline/monthly-maintenance.log"
DATE=$(date '+%Y-%m-%d %H:%M:%S')

echo "[$DATE] Starting monthly maintenance..." >> $LOG_FILE

# 1. Full Database Backup
echo "[$DATE] Creating full database backup..." >> $LOG_FILE
pg_dump clearline_production | gzip > /backup/monthly_$(date +%Y%m%d).sql.gz

# 2. Database Optimization
echo "[$DATE] Running database optimization..." >> $LOG_FILE
cd /path/to/clearline/custom/laravel
php artisan tinker --execute="
DB::statement('REINDEX DATABASE clearline_production;');
DB::statement('VACUUM FULL;');
" >> $LOG_FILE 2>&1

# 3. Security Updates
echo "[$DATE] Checking for security updates..." >> $LOG_FILE
sudo apt update
sudo apt list --upgradable | grep -i security >> $LOG_FILE

# 4. SSL Certificate Check
echo "[$DATE] Checking SSL certificates..." >> $LOG_FILE
echo | openssl s_client -servername your-domain.com -connect your-domain.com:443 2>/dev/null | openssl x509 -noout -dates >> $LOG_FILE

# 5. Capacity Analysis
echo "[$DATE] Running capacity analysis..." >> $LOG_FILE
echo "Database size:" >> $LOG_FILE
sudo -u postgres psql clearline_production -c "
SELECT pg_size_pretty(pg_database_size('clearline_production'));
" >> $LOG_FILE

echo "Table sizes:" >> $LOG_FILE
sudo -u postgres psql clearline_production -c "
SELECT schemaname,tablename,pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) as size
FROM pg_tables
WHERE schemaname = 'public'
ORDER BY pg_total_relation_size(schemaname||'.'||tablename) DESC
LIMIT 10;
" >> $LOG_FILE

# 6. Performance Benchmarking
echo "[$DATE] Running performance benchmarks..." >> $LOG_FILE
ab -n 100 -c 10 https://your-domain.com/health >> $LOG_FILE 2>&1

echo "[$DATE] Monthly maintenance completed." >> $LOG_FILE
```

### Monthly Checklist

**Manual Monthly Tasks:**

- [ ] Review and update disaster recovery plan
- [ ] Conduct security audit
- [ ] Review and update monitoring alerts
- [ ] Analyze capacity trends and plan scaling
- [ ] Review and update documentation
- [ ] Test failover procedures
- [ ] Review third-party service integrations
- [ ] Update SSL certificates if needed
- [ ] Review and optimize application performance
- [ ] Plan major updates and feature releases
- [ ] Review team access and permissions
- [ ] Conduct post-incident reviews

### Monthly Reporting

Generate monthly reports covering:

```bash
# Monthly performance report
cat > /tmp/monthly_report.sql << 'EOF'
-- Conversation statistics
SELECT 
    DATE_TRUNC('day', created_at) as date,
    COUNT(*) as conversations_created
FROM conversations 
WHERE created_at >= NOW() - INTERVAL '30 days'
GROUP BY DATE_TRUNC('day', created_at)
ORDER BY date;

-- Message statistics
SELECT 
    DATE_TRUNC('day', created_at) as date,
    COUNT(*) as messages_sent
FROM messages 
WHERE created_at >= NOW() - INTERVAL '30 days'
GROUP BY DATE_TRUNC('day', created_at)
ORDER BY date;

-- User activity
SELECT 
    COUNT(DISTINCT user_id) as active_users
FROM personal_access_tokens 
WHERE last_used_at >= NOW() - INTERVAL '30 days';

-- Top channels by usage
SELECT 
    channel_type,
    COUNT(*) as conversation_count
FROM conversations c
JOIN inboxes i ON c.inbox_id = i.id
WHERE c.created_at >= NOW() - INTERVAL '30 days'
GROUP BY channel_type
ORDER BY conversation_count DESC;
EOF

sudo -u postgres psql clearline_production -f /tmp/monthly_report.sql > /tmp/monthly_stats.txt
```

## Quarterly Maintenance Tasks

### Quarterly Checklist

**Strategic Quarterly Tasks:**

- [ ] Comprehensive security audit and penetration testing
- [ ] Review and update business continuity plan
- [ ] Capacity planning and infrastructure scaling review
- [ ] Major version updates and migrations
- [ ] Performance optimization and architecture review
- [ ] Third-party service contract reviews
- [ ] Compliance audit (GDPR, SOC2, etc.)
- [ ] Team training and knowledge sharing sessions
- [ ] Technology stack evaluation and updates
- [ ] Cost optimization review

### Quarterly Security Audit

```bash
#!/bin/bash
# Quarterly security audit script

echo "=== ClearLine Security Audit ===" > /tmp/security_audit.txt
echo "Date: $(date)" >> /tmp/security_audit.txt
echo "" >> /tmp/security_audit.txt

# Check for outdated packages
echo "=== Outdated Packages ===" >> /tmp/security_audit.txt
composer outdated >> /tmp/security_audit.txt
echo "" >> /tmp/security_audit.txt

# Check for security vulnerabilities
echo "=== Security Vulnerabilities ===" >> /tmp/security_audit.txt
composer audit >> /tmp/security_audit.txt
echo "" >> /tmp/security_audit.txt

# Check SSL configuration
echo "=== SSL Configuration ===" >> /tmp/security_audit.txt
nmap --script ssl-enum-ciphers -p 443 your-domain.com >> /tmp/security_audit.txt
echo "" >> /tmp/security_audit.txt

# Check file permissions
echo "=== File Permissions ===" >> /tmp/security_audit.txt
find /path/to/clearline -type f -perm -002 >> /tmp/security_audit.txt
echo "" >> /tmp/security_audit.txt

# Check for exposed sensitive files
echo "=== Sensitive Files Check ===" >> /tmp/security_audit.txt
find /path/to/clearline -name ".env*" -o -name "*.key" -o -name "*.pem" >> /tmp/security_audit.txt
```

## Performance Monitoring

### Key Performance Indicators (KPIs)

**Application Performance:**
- Response time (target: <200ms for 95% of requests)
- Throughput (requests per second)
- Error rate (target: <1%)
- Availability (target: 99.9%)

**Infrastructure Performance:**
- CPU usage (target: <70% average)
- Memory usage (target: <80%)
- Disk I/O (IOPS and latency)
- Network throughput

**Database Performance:**
- Query response time
- Connection pool usage
- Lock contention
- Index efficiency

### Monitoring Setup

```bash
# Install monitoring tools
sudo apt install -y htop iotop nethogs

# Set up performance monitoring script
cat > /usr/local/bin/performance-monitor.sh << 'EOF'
#!/bin/bash

TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')
LOG_FILE="/var/log/clearline/performance.log"

# System metrics
CPU_USAGE=$(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | cut -d'%' -f1)
MEMORY_USAGE=$(free | grep Mem | awk '{printf "%.1f", $3/$2 * 100.0}')
DISK_USAGE=$(df -h / | awk 'NR==2{print $5}' | cut -d'%' -f1)

# Database metrics
DB_CONNECTIONS=$(sudo -u postgres psql -t -c "SELECT count(*) FROM pg_stat_activity;")
DB_SIZE=$(sudo -u postgres psql -t -c "SELECT pg_size_pretty(pg_database_size('clearline_production'));")

# Application metrics
ACTIVE_SESSIONS=$(redis-cli eval "return #redis.call('keys', 'laravel_session:*')" 0)
QUEUE_SIZE=$(redis-cli llen "queues:default")

echo "$TIMESTAMP,CPU:$CPU_USAGE%,Memory:$MEMORY_USAGE%,Disk:$DISK_USAGE%,DB_Conn:$DB_CONNECTIONS,DB_Size:$DB_SIZE,Sessions:$ACTIVE_SESSIONS,Queue:$QUEUE_SIZE" >> $LOG_FILE
EOF

chmod +x /usr/local/bin/performance-monitor.sh

# Add to crontab (every 5 minutes)
echo "*/5 * * * * /usr/local/bin/performance-monitor.sh" | crontab -
```

### Performance Alerting

```bash
# Create alerting script
cat > /usr/local/bin/performance-alerts.sh << 'EOF'
#!/bin/bash

ALERT_EMAIL="admin@your-domain.com"

# Check CPU usage
CPU_USAGE=$(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | cut -d'%' -f1 | cut -d'.' -f1)
if [ "$CPU_USAGE" -gt 80 ]; then
    echo "High CPU usage: $CPU_USAGE%" | mail -s "ClearLine Alert: High CPU" $ALERT_EMAIL
fi

# Check memory usage
MEMORY_USAGE=$(free | grep Mem | awk '{printf "%.0f", $3/$2 * 100.0}')
if [ "$MEMORY_USAGE" -gt 85 ]; then
    echo "High memory usage: $MEMORY_USAGE%" | mail -s "ClearLine Alert: High Memory" $ALERT_EMAIL
fi

# Check disk usage
DISK_USAGE=$(df -h / | awk 'NR==2{print $5}' | cut -d'%' -f1)
if [ "$DISK_USAGE" -gt 85 ]; then
    echo "High disk usage: $DISK_USAGE%" | mail -s "ClearLine Alert: High Disk Usage" $ALERT_EMAIL
fi

# Check failed jobs
FAILED_JOBS=$(cd /path/to/clearline/custom/laravel && php artisan queue:failed --format=json | jq length)
if [ "$FAILED_JOBS" -gt 50 ]; then
    echo "High number of failed jobs: $FAILED_JOBS" | mail -s "ClearLine Alert: Failed Jobs" $ALERT_EMAIL
fi
EOF

chmod +x /usr/local/bin/performance-alerts.sh

# Run every 10 minutes
echo "*/10 * * * * /usr/local/bin/performance-alerts.sh" | crontab -
```

## Database Maintenance

### Daily Database Tasks

```bash
# Database health check
sudo -u postgres psql clearline_production -c "
SELECT 
    schemaname,
    tablename,
    attname,
    n_distinct,
    correlation
FROM pg_stats 
WHERE schemaname = 'public' 
ORDER BY n_distinct DESC 
LIMIT 10;
"

# Check for long-running queries
sudo -u postgres psql clearline_production -c "
SELECT 
    pid,
    now() - pg_stat_activity.query_start AS duration,
    query 
FROM pg_stat_activity 
WHERE (now() - pg_stat_activity.query_start) > interval '5 minutes';
"
```

### Weekly Database Tasks

```bash
# Update table statistics
sudo -u postgres psql clearline_production -c "ANALYZE;"

# Check for unused indexes
sudo -u postgres psql clearline_production -c "
SELECT 
    schemaname,
    tablename,
    indexname,
    idx_tup_read,
    idx_tup_fetch
FROM pg_stat_user_indexes 
WHERE idx_tup_read = 0 
AND idx_tup_fetch = 0;
"

# Check index usage
sudo -u postgres psql clearline_production -c "
SELECT 
    schemaname,
    tablename,
    indexname,
    idx_scan,
    idx_tup_read,
    idx_tup_fetch
FROM pg_stat_user_indexes 
ORDER BY idx_scan DESC;
"
```

### Monthly Database Tasks

```bash
# Full vacuum and reindex
sudo -u postgres psql clearline_production -c "VACUUM FULL;"
sudo -u postgres psql clearline_production -c "REINDEX DATABASE clearline_production;"

# Check database bloat
sudo -u postgres psql clearline_production -c "
SELECT 
    schemaname,
    tablename,
    pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) as size,
    pg_size_pretty(pg_relation_size(schemaname||'.'||tablename)) as table_size,
    pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename) - pg_relation_size(schemaname||'.'||tablename)) as index_size
FROM pg_tables 
WHERE schemaname = 'public' 
ORDER BY pg_total_relation_size(schemaname||'.'||tablename) DESC;
"
```

## Security Maintenance

### Daily Security Tasks

```bash
# Check for failed login attempts
grep "authentication failure" /var/log/auth.log | tail -20

# Check for suspicious network activity
netstat -tuln | grep LISTEN

# Check for unauthorized file changes
find /path/to/clearline -type f -mtime -1 -name "*.php" | head -20
```

### Weekly Security Tasks

```bash
# Update security patches
sudo apt update
sudo apt list --upgradable | grep -i security

# Check SSL certificate status
echo | openssl s_client -servername your-domain.com -connect your-domain.com:443 2>/dev/null | openssl x509 -noout -dates

# Review access logs for suspicious activity
awk '{print $1}' /var/log/nginx/clearline_access.log | sort | uniq -c | sort -nr | head -20
```

### Monthly Security Tasks

```bash
# Full security audit
composer audit
npm audit

# Check for exposed sensitive information
grep -r "password\|secret\|key" /path/to/clearline --exclude-dir=vendor --exclude-dir=node_modules

# Review user permissions
cd /path/to/clearline/custom/laravel
php artisan tinker --execute="
User::with('roles')->get()->each(function(\$user) {
    echo \$user->email . ': ' . \$user->roles->pluck('name')->join(', ') . PHP_EOL;
});
"
```

## Backup and Recovery

### Automated Backup Script

```bash
#!/bin/bash
# /usr/local/bin/clearline-backup.sh

BACKUP_DIR="/var/backups/clearline"
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=30

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
echo "Creating database backup..."
pg_dump -U clearline -h localhost clearline_production | gzip > $BACKUP_DIR/db_backup_$DATE.sql.gz

# Application files backup
echo "Creating application files backup..."
tar -czf $BACKUP_DIR/files_backup_$DATE.tar.gz \
    --exclude='vendor' \
    --exclude='node_modules' \
    --exclude='storage/logs' \
    --exclude='storage/framework/cache' \
    /path/to/clearline/custom/laravel

# Configuration backup
echo "Creating configuration backup..."
tar -czf $BACKUP_DIR/config_backup_$DATE.tar.gz \
    /etc/nginx/sites-available/clearline \
    /etc/php/8.2/fpm/pool.d/clearline.conf \
    /etc/supervisor/conf.d/clearline-*.conf

# Clean old backups
echo "Cleaning old backups..."
find $BACKUP_DIR -name "*.gz" -mtime +$RETENTION_DAYS -delete

# Verify backup integrity
echo "Verifying backup integrity..."
gunzip -t $BACKUP_DIR/db_backup_$DATE.sql.gz
if [ $? -eq 0 ]; then
    echo "Database backup verified successfully"
else
    echo "ERROR: Database backup verification failed"
    exit 1
fi

echo "Backup completed successfully: $DATE"
```

### Backup Verification

```bash
#!/bin/bash
# Test backup restoration

TEST_DB="clearline_backup_test"
LATEST_BACKUP=$(ls -t /var/backups/clearline/db_backup_*.sql.gz | head -1)

echo "Testing backup restoration with: $LATEST_BACKUP"

# Create test database
sudo -u postgres createdb $TEST_DB

# Restore backup
gunzip -c $LATEST_BACKUP | sudo -u postgres psql $TEST_DB

# Verify restoration
TABLE_COUNT=$(sudo -u postgres psql -t $TEST_DB -c "SELECT count(*) FROM information_schema.tables WHERE table_schema = 'public';")
echo "Restored $TABLE_COUNT tables"

# Cleanup
sudo -u postgres dropdb $TEST_DB

echo "Backup verification completed"
```

## Update Procedures

### Application Updates

```bash
#!/bin/bash
# Application update procedure

echo "Starting application update..."

# 1. Enable maintenance mode
php artisan down --message="System update in progress"

# 2. Create backup
/usr/local/bin/clearline-backup.sh

# 3. Pull latest code
git fetch origin
git checkout main
git pull origin main

# 4. Update dependencies
composer install --no-dev --optimize-autoloader

# 5. Run migrations
php artisan migrate --force

# 6. Clear and rebuild caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Restart services
sudo supervisorctl restart clearline-horizon
sudo supervisorctl restart clearline-reverb

# 8. Run tests
php artisan test --env=production

# 9. Disable maintenance mode
php artisan up

echo "Application update completed"
```

### System Updates

```bash
#!/bin/bash
# System update procedure

echo "Starting system update..."

# 1. Update package lists
sudo apt update

# 2. List available updates
sudo apt list --upgradable

# 3. Install security updates
sudo apt upgrade -y

# 4. Restart services if needed
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
sudo systemctl restart postgresql
sudo systemctl restart redis-server

# 5. Verify services
systemctl is-active nginx php8.2-fpm postgresql redis-server

echo "System update completed"
```

## Capacity Planning

### Resource Monitoring

```bash
# Monitor resource trends
cat > /usr/local/bin/capacity-monitor.sh << 'EOF'
#!/bin/bash

LOG_FILE="/var/log/clearline/capacity.log"
TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')

# Database size growth
DB_SIZE=$(sudo -u postgres psql -t clearline_production -c "SELECT pg_database_size('clearline_production');")

# Table growth
CONVERSATIONS=$(sudo -u postgres psql -t clearline_production -c "SELECT count(*) FROM conversations;")
MESSAGES=$(sudo -u postgres psql -t clearline_production -c "SELECT count(*) FROM messages;")
CONTACTS=$(sudo -u postgres psql -t clearline_production -c "SELECT count(*) FROM contacts;")

# Storage usage
STORAGE_USED=$(du -sb /path/to/clearline/custom/laravel/storage | cut -f1)

# Log metrics
echo "$TIMESTAMP,$DB_SIZE,$CONVERSATIONS,$MESSAGES,$CONTACTS,$STORAGE_USED" >> $LOG_FILE
EOF

chmod +x /usr/local/bin/capacity-monitor.sh

# Run daily
echo "0 2 * * * /usr/local/bin/capacity-monitor.sh" | crontab -
```

### Scaling Recommendations

**Database Scaling:**
- Monitor query performance and add indexes as needed
- Consider read replicas for high-read workloads
- Plan for database partitioning for large tables

**Application Scaling:**
- Monitor response times and add application servers as needed
- Implement load balancing for multiple application instances
- Consider caching strategies for frequently accessed data

**Infrastructure Scaling:**
- Monitor resource usage and plan capacity increases
- Consider auto-scaling for cloud deployments
- Plan for disaster recovery and high availability

## Incident Response

### Incident Classification

| Severity | Description | Response Time | Examples |
|----------|-------------|---------------|----------|
| **Critical** | System down, data loss | 15 minutes | Database corruption, security breach |
| **High** | Major functionality impaired | 1 hour | API endpoints failing, authentication issues |
| **Medium** | Minor functionality impaired | 4 hours | Single channel not working, slow performance |
| **Low** | Cosmetic issues, minor bugs | 24 hours | UI glitches, documentation errors |

### Incident Response Procedures

**Critical Incident Response:**

1. **Immediate Response (0-15 minutes):**
   ```bash
   # Put system in maintenance mode
   php artisan down --message="Emergency maintenance in progress"
   
   # Assess the situation
   systemctl status nginx php8.2-fpm postgresql redis-server
   tail -100 storage/logs/laravel.log
   ```

2. **Investigation (15-30 minutes):**
   ```bash
   # Check system resources
   htop
   df -h
   free -h
   
   # Check database
   sudo -u postgres psql clearline_production -c "SELECT version();"
   
   # Check recent changes
   git log --oneline -10
   ```

3. **Resolution (30+ minutes):**
   ```bash
   # Restore from backup if needed
   /usr/local/bin/restore-backup.sh
   
   # Or fix the issue and restart services
   sudo systemctl restart nginx php8.2-fpm
   sudo supervisorctl restart all
   
   # Bring system back online
   php artisan up
   ```

### Post-Incident Review

After resolving any incident:

1. **Document the incident:**
   - Timeline of events
   - Root cause analysis
   - Actions taken
   - Lessons learned

2. **Implement improvements:**
   - Update monitoring and alerting
   - Improve documentation
   - Add preventive measures
   - Update incident response procedures

3. **Communicate with stakeholders:**
   - Notify affected users
   - Provide status updates
   - Share lessons learned

---

**Last Updated:** 2025-01-02  
**Version:** 1.0  
**Maintainer:** Development Team