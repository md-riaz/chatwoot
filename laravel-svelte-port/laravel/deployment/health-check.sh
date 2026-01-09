#!/bin/bash

# ClearLine Health Check Script
# Monitors application health and services

set -e

# Configuration
APP_DIR="/var/www/clearline"
LOG_FILE="/var/log/clearline-health.log"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ERROR:${NC} $1" | tee -a "$LOG_FILE"
}

warning() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] WARNING:${NC} $1" | tee -a "$LOG_FILE"
}

# Health check results
HEALTH_STATUS=0

log "Starting ClearLine health check..."

# Check if application directory exists
if [ ! -d "$APP_DIR" ]; then
    error "Application directory not found: $APP_DIR"
    HEALTH_STATUS=1
fi

cd "$APP_DIR" 2>/dev/null || exit 1

# Check .env file
if [ ! -f ".env" ]; then
    error ".env file not found"
    HEALTH_STATUS=1
else
    log "✓ .env file exists"
fi

# Check database connection
if sudo -u www-data php artisan migrate:status >/dev/null 2>&1; then
    log "✓ Database connection successful"
else
    error "✗ Database connection failed"
    HEALTH_STATUS=1
fi

# Check Redis connection
if sudo -u www-data php artisan tinker --execute="Redis::ping()" 2>/dev/null | grep -q "PONG"; then
    log "✓ Redis connection successful"
else
    warning "✗ Redis connection failed"
    HEALTH_STATUS=1
fi

# Check Nginx status
if sudo systemctl is-active --quiet nginx 2>/dev/null; then
    log "✓ Nginx is running"
else
    error "✗ Nginx is not running"
    HEALTH_STATUS=1
fi

# Check PHP-FPM status
if sudo systemctl is-active --quiet php8.2-fpm 2>/dev/null; then
    log "✓ PHP-FPM is running"
else
    error "✗ PHP-FPM is not running"
    HEALTH_STATUS=1
fi

# Check Supervisor services
if command -v supervisorctl >/dev/null 2>&1; then
    # Check Reverb
    if sudo supervisorctl status clearline-reverb:* 2>/dev/null | grep -q RUNNING; then
        log "✓ Reverb WebSocket server is running"
    else
        error "✗ Reverb WebSocket server is not running"
        HEALTH_STATUS=1
    fi
    
    # Check Horizon
    if sudo supervisorctl status clearline-horizon:* 2>/dev/null | grep -q RUNNING; then
        log "✓ Horizon queue manager is running"
    else
        error "✗ Horizon queue manager is not running"
        HEALTH_STATUS=1
    fi
    
    # Check Workers
    if sudo supervisorctl status clearline-worker:* 2>/dev/null | grep -q RUNNING; then
        log "✓ Queue workers are running"
    else
        warning "✗ Queue workers are not running"
    fi
else
    # WSL environment - check processes directly
    if pgrep -f "artisan reverb:start" >/dev/null; then
        log "✓ Reverb WebSocket server is running"
    else
        error "✗ Reverb WebSocket server is not running"
        HEALTH_STATUS=1
    fi
    
    if pgrep -f "artisan horizon" >/dev/null; then
        log "✓ Horizon queue manager is running"
    else
        error "✗ Horizon queue manager is not running"
        HEALTH_STATUS=1
    fi
fi

# Check WebSocket port
REVERB_PORT=$(grep REVERB_PORT= .env | cut -d'=' -f2)
if [ -z "$REVERB_PORT" ]; then
    REVERB_PORT=8080
fi

if nc -z localhost "$REVERB_PORT" 2>/dev/null; then
    log "✓ WebSocket server is listening on port $REVERB_PORT"
else
    error "✗ WebSocket server is not listening on port $REVERB_PORT"
    HEALTH_STATUS=1
fi

# Check HTTP response
if curl -f -s http://localhost >/dev/null 2>&1; then
    log "✓ Application is responding to HTTP requests"
else
    error "✗ Application is not responding to HTTP requests"
    HEALTH_STATUS=1
fi

# Check API endpoint
if curl -f -s http://localhost/api/health >/dev/null 2>&1; then
    log "✓ API health endpoint is responding"
else
    warning "✗ API health endpoint is not responding (may not be implemented)"
fi

# Check frontend assets
if [ -f "public/app/index.html" ]; then
    log "✓ Frontend assets are deployed"
else
    warning "✗ Frontend assets not found - run build-frontend.sh"
fi

# Check storage permissions
if [ -w "storage/logs" ]; then
    log "✓ Storage directory is writable"
else
    error "✗ Storage directory is not writable"
    HEALTH_STATUS=1
fi

# Check log files for recent errors
if [ -f "storage/logs/laravel.log" ]; then
    ERROR_COUNT=$(tail -n 100 storage/logs/laravel.log | grep -c "ERROR" || true)
    if [ "$ERROR_COUNT" -gt 0 ]; then
        warning "Found $ERROR_COUNT recent errors in Laravel log"
    else
        log "✓ No recent errors in Laravel log"
    fi
fi

# Disk space check
DISK_USAGE=$(df "$APP_DIR" | awk 'NR==2 {print $5}' | sed 's/%//')
if [ "$DISK_USAGE" -gt 90 ]; then
    error "Disk usage is at ${DISK_USAGE}% - critically high"
    HEALTH_STATUS=1
elif [ "$DISK_USAGE" -gt 80 ]; then
    warning "Disk usage is at ${DISK_USAGE}% - consider cleanup"
else
    log "✓ Disk usage is at ${DISK_USAGE}% - healthy"
fi

# Memory check
MEMORY_USAGE=$(free | awk 'NR==2{printf "%.0f", $3*100/$2}')
if [ "$MEMORY_USAGE" -gt 90 ]; then
    warning "Memory usage is at ${MEMORY_USAGE}% - high"
elif [ "$MEMORY_USAGE" -gt 95 ]; then
    error "Memory usage is at ${MEMORY_USAGE}% - critically high"
    HEALTH_STATUS=1
else
    log "✓ Memory usage is at ${MEMORY_USAGE}% - healthy"
fi

# Summary
echo ""
if [ $HEALTH_STATUS -eq 0 ]; then
    log "🎉 All health checks passed - system is healthy!"
else
    error "❌ Health check failed - please review errors above"
fi

echo ""
echo "=== Health Check Summary ==="
echo "Timestamp: $(date)"
echo "Application Directory: $APP_DIR"
echo "Log File: $LOG_FILE"
echo "Overall Status: $([ $HEALTH_STATUS -eq 0 ] && echo "HEALTHY" || echo "UNHEALTHY")"
echo ""

exit $HEALTH_STATUS