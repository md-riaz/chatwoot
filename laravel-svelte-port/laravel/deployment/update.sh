#!/bin/bash

# ClearLine Update Script
# Safely updates the application with zero-downtime deployment

set -e

# Configuration
APP_DIR="/var/www/clearline"
BACKUP_DIR="/var/backups/clearline"
LOG_FILE="/var/log/clearline-update.log"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ERROR:${NC} $1" | tee -a "$LOG_FILE"
    exit 1
}

warning() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] WARNING:${NC} $1" | tee -a "$LOG_FILE"
}

info() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')] INFO:${NC} $1" | tee -a "$LOG_FILE"
}

# Check if running as correct user
if [[ $EUID -eq 0 ]]; then
   error "This script should not be run as root for security reasons"
fi

# Verify required commands
command -v php >/dev/null 2>&1 || error "PHP is not installed"
command -v composer >/dev/null 2>&1 || error "Composer is not installed"

log "Starting ClearLine update process..."

# Check if application exists
if [ ! -d "$APP_DIR" ]; then
    error "Application directory not found: $APP_DIR"
fi

cd "$APP_DIR"

# Check if .env exists
if [ ! -f ".env" ]; then
    error ".env file not found"
fi

# Get current version info
CURRENT_VERSION=$(git describe --tags --always 2>/dev/null || echo "unknown")
log "Current version: $CURRENT_VERSION"

# Create backup before update
log "Creating pre-update backup..."
if [ -f "$SCRIPT_DIR/backup.sh" ]; then
    sudo bash "$SCRIPT_DIR/backup.sh"
    log "✓ Backup completed"
else
    warning "Backup script not found - proceeding without backup"
fi

# Put application in maintenance mode
log "Enabling maintenance mode..."
sudo -u www-data php artisan down --retry=60 --secret="clearline-update-$(date +%s)"
MAINTENANCE_SECRET=$(grep -o 'clearline-update-[0-9]*' storage/framework/down 2>/dev/null || echo "")

# Update application code
log "Updating application code..."
if [ -d "$PROJECT_ROOT" ]; then
    # Copy new code
    sudo cp -r "$PROJECT_ROOT"/* "$APP_DIR/"
    sudo chown -R www-data:www-data "$APP_DIR"
    log "✓ Application code updated"
else
    warning "Source directory not found - skipping code update"
fi

# Install/update Composer dependencies
log "Updating Composer dependencies..."
sudo -u www-data composer install --no-dev --optimize-autoloader --no-interaction

# Update Node.js dependencies and rebuild frontend (optional)
log "Frontend build available..."
if [ -f "$SCRIPT_DIR/build-frontend.sh" ]; then
    echo "Frontend build script found. Build frontend? (y/n)"
    read -p "Build frontend now? [y/N]: " -r
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        bash "$SCRIPT_DIR/build-frontend.sh"
        log "✓ Frontend rebuilt"
    else
        log "Skipping frontend build - run ./deployment/build-frontend.sh manually if needed"
    fi
else
    warning "Frontend build script not found at $SCRIPT_DIR/build-frontend.sh"
fi

# Run database migrations
log "Running database migrations..."
sudo -u www-data php artisan migrate --force

# Clear and rebuild caches
log "Rebuilding application caches..."
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan cache:clear

# Rebuild optimized caches
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo -u www-data php artisan event:cache

# Update storage link
sudo -u www-data php artisan storage:link

# Restart services
log "Restarting services..."

# Check if we're using Supervisor (production) or manual processes (WSL)
if command -v supervisorctl >/dev/null 2>&1; then
    # Production environment with Supervisor
    log "Restarting Supervisor services..."
    
    # Restart Horizon (queue manager)
    if sudo supervisorctl status clearline-horizon:* >/dev/null 2>&1; then
        sudo supervisorctl restart clearline-horizon:*
        log "✓ Horizon restarted"
    fi
    
    # Restart Reverb (WebSocket server)
    if sudo supervisorctl status clearline-reverb:* >/dev/null 2>&1; then
        sudo supervisorctl restart clearline-reverb:*
        log "✓ Reverb restarted"
    fi
    
    # Restart queue workers
    if sudo supervisorctl status clearline-worker:* >/dev/null 2>&1; then
        sudo supervisorctl restart clearline-worker:*
        log "✓ Queue workers restarted"
    fi
else
    # WSL environment - restart manual processes
    log "Restarting WSL services..."
    
    # Stop existing processes
    if [ -f "/tmp/reverb.pid" ]; then
        kill $(cat /tmp/reverb.pid) 2>/dev/null || true
        rm -f /tmp/reverb.pid
    fi
    
    if [ -f "/tmp/horizon.pid" ]; then
        kill $(cat /tmp/horizon.pid) 2>/dev/null || true
        rm -f /tmp/horizon.pid
    fi
    
    # Wait for processes to stop
    sleep 3
    
    # Start Reverb WebSocket server
    sudo -u www-data nohup php artisan reverb:start --host=0.0.0.0 --port=8080 > storage/logs/reverb.log 2>&1 &
    echo $! > /tmp/reverb.pid
    
    # Start Horizon queue manager
    sudo -u www-data nohup php artisan horizon > storage/logs/horizon.log 2>&1 &
    echo $! > /tmp/horizon.pid
    
    log "✓ WSL services restarted"
fi

# Reload Nginx configuration
if sudo systemctl is-active --quiet nginx 2>/dev/null; then
    sudo systemctl reload nginx
    log "✓ Nginx configuration reloaded"
elif sudo service nginx status >/dev/null 2>&1; then
    sudo service nginx reload
    log "✓ Nginx configuration reloaded"
fi

# Wait for services to start
log "Waiting for services to start..."
sleep 5

# Verify services are running
log "Verifying services..."

# Check WebSocket server
REVERB_PORT=$(grep REVERB_PORT= .env | cut -d'=' -f2)
if [ -z "$REVERB_PORT" ]; then
    REVERB_PORT=8080
fi

if nc -z localhost "$REVERB_PORT" 2>/dev/null; then
    log "✓ WebSocket server is running on port $REVERB_PORT"
else
    warning "✗ WebSocket server is not responding"
fi

# Check HTTP response
if curl -f -s http://localhost >/dev/null 2>&1; then
    log "✓ Application is responding"
else
    error "✗ Application is not responding - rolling back"
fi

# Run health check if available
if [ -f "$SCRIPT_DIR/health-check.sh" ]; then
    log "Running health check..."
    if bash "$SCRIPT_DIR/health-check.sh" >/dev/null 2>&1; then
        log "✓ Health check passed"
    else
        warning "Health check failed - check logs"
    fi
fi

# Disable maintenance mode
log "Disabling maintenance mode..."
sudo -u www-data php artisan up
log "✓ Application is back online"

# Get new version info
NEW_VERSION=$(git describe --tags --always 2>/dev/null || echo "unknown")
log "Updated to version: $NEW_VERSION"

# Clean up old cache files
log "Cleaning up temporary files..."
sudo -u www-data php artisan optimize:clear >/dev/null 2>&1 || true

echo ""
echo "=== Update Summary ==="
echo "Previous Version: $CURRENT_VERSION"
echo "Current Version: $NEW_VERSION"
echo "Application Directory: $APP_DIR"
echo "Log File: $LOG_FILE"
echo "Maintenance Secret: $MAINTENANCE_SECRET"
echo ""
echo "=== Verification ==="
echo "Application URL: http://$(hostname -I | awk '{print $1}')"
echo "Frontend SPA: http://$(hostname -I | awk '{print $1}')/app"
echo "API Health: http://$(hostname -I | awk '{print $1}')/api/health"
echo ""
echo "=== Rollback Instructions (if needed) ==="
echo "1. Enable maintenance: php artisan down"
echo "2. Restore from backup: $BACKUP_DIR/latest"
echo "3. Restart services and disable maintenance"
echo ""

log "Update completed successfully!"

# Optional: Send notification (webhook, email, etc.)
if [ -n "$UPDATE_WEBHOOK_URL" ]; then
    curl -X POST "$UPDATE_WEBHOOK_URL" \
        -H "Content-Type: application/json" \
        -d "{\"message\":\"ClearLine updated from $CURRENT_VERSION to $NEW_VERSION\",\"status\":\"success\"}" \
        >/dev/null 2>&1 || true
fi