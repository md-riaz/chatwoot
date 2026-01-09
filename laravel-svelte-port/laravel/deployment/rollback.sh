#!/bin/bash

# ClearLine Rollback Script
# Safely rolls back to a previous backup

set -e

# Configuration
APP_DIR="/var/www/clearline"
BACKUP_BASE_DIR="/var/backups/clearline"
LOG_FILE="/var/log/clearline-rollback.log"

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

show_usage() {
    echo "Usage: $0 [BACKUP_TIMESTAMP]"
    echo ""
    echo "Options:"
    echo "  BACKUP_TIMESTAMP    Specific backup to restore (format: YYYYMMDD-HHMMSS)"
    echo "                      If not provided, will use the latest backup"
    echo ""
    echo "Examples:"
    echo "  $0                  # Restore from latest backup"
    echo "  $0 20240108-143022  # Restore from specific backup"
    echo ""
    echo "Available backups:"
    if [ -d "$BACKUP_BASE_DIR" ]; then
        find "$BACKUP_BASE_DIR" -type d -name "20*" | sort -r | head -10 | while read backup; do
            timestamp=$(basename "$backup")
            size=$(du -sh "$backup" 2>/dev/null | cut -f1 || echo "unknown")
            echo "  $timestamp ($size)"
        done
    else
        echo "  No backups found in $BACKUP_BASE_DIR"
    fi
}

# Check if running as correct user
if [[ $EUID -eq 0 ]]; then
   error "This script should not be run as root for security reasons"
fi

# Parse command line arguments
BACKUP_TIMESTAMP="$1"

if [ "$BACKUP_TIMESTAMP" = "-h" ] || [ "$BACKUP_TIMESTAMP" = "--help" ]; then
    show_usage
    exit 0
fi

# Check if backup directory exists
if [ ! -d "$BACKUP_BASE_DIR" ]; then
    error "Backup directory not found: $BACKUP_BASE_DIR"
fi

# Determine which backup to use
if [ -n "$BACKUP_TIMESTAMP" ]; then
    BACKUP_DIR="$BACKUP_BASE_DIR/$BACKUP_TIMESTAMP"
    if [ ! -d "$BACKUP_DIR" ]; then
        error "Backup not found: $BACKUP_DIR"
    fi
else
    # Use latest backup
    if [ -L "$BACKUP_BASE_DIR/latest" ]; then
        BACKUP_DIR=$(readlink -f "$BACKUP_BASE_DIR/latest")
        BACKUP_TIMESTAMP=$(basename "$BACKUP_DIR")
    else
        # Find most recent backup
        BACKUP_DIR=$(find "$BACKUP_BASE_DIR" -type d -name "20*" | sort -r | head -1)
        if [ -z "$BACKUP_DIR" ]; then
            error "No backups found in $BACKUP_BASE_DIR"
        fi
        BACKUP_TIMESTAMP=$(basename "$BACKUP_DIR")
    fi
fi

log "Starting rollback to backup: $BACKUP_TIMESTAMP"

# Verify backup integrity
log "Verifying backup integrity..."

if [ ! -f "$BACKUP_DIR/manifest.txt" ]; then
    warning "Backup manifest not found - proceeding anyway"
else
    log "✓ Backup manifest found"
fi

if [ ! -f "$BACKUP_DIR/application.tar.gz" ]; then
    error "Application backup not found: $BACKUP_DIR/application.tar.gz"
fi

log "✓ Backup verification completed"

# Show backup information
if [ -f "$BACKUP_DIR/manifest.txt" ]; then
    echo ""
    echo "=== Backup Information ==="
    head -20 "$BACKUP_DIR/manifest.txt"
    echo ""
fi

# Confirm rollback
echo ""
warning "This will rollback the application to backup: $BACKUP_TIMESTAMP"
warning "Current application data will be lost!"
echo ""
read -p "Are you sure you want to continue? (yes/no): " -r
if [[ ! $REPLY =~ ^[Yy][Ee][Ss]$ ]]; then
    log "Rollback cancelled by user"
    exit 0
fi

# Check if application exists
if [ ! -d "$APP_DIR" ]; then
    error "Application directory not found: $APP_DIR"
fi

cd "$APP_DIR"

# Put application in maintenance mode
log "Enabling maintenance mode..."
sudo -u www-data php artisan down --retry=60 --secret="clearline-rollback-$(date +%s)" 2>/dev/null || true

# Stop services
log "Stopping services..."

if command -v supervisorctl >/dev/null 2>&1; then
    # Production environment with Supervisor
    sudo supervisorctl stop clearline-horizon:* 2>/dev/null || true
    sudo supervisorctl stop clearline-reverb:* 2>/dev/null || true
    sudo supervisorctl stop clearline-worker:* 2>/dev/null || true
    log "✓ Supervisor services stopped"
else
    # WSL environment - stop manual processes
    if [ -f "/tmp/reverb.pid" ]; then
        kill $(cat /tmp/reverb.pid) 2>/dev/null || true
        rm -f /tmp/reverb.pid
    fi
    
    if [ -f "/tmp/horizon.pid" ]; then
        kill $(cat /tmp/horizon.pid) 2>/dev/null || true
        rm -f /tmp/horizon.pid
    fi
    
    log "✓ WSL services stopped"
fi

# Wait for processes to stop
sleep 3

# Create rollback backup of current state
log "Creating rollback backup of current state..."
ROLLBACK_BACKUP_DIR="$BACKUP_BASE_DIR/rollback-$(date +%Y%m%d-%H%M%S)"
mkdir -p "$ROLLBACK_BACKUP_DIR"

tar -czf "$ROLLBACK_BACKUP_DIR/current-application.tar.gz" \
    --exclude='vendor' \
    --exclude='node_modules' \
    --exclude='storage/logs/*' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='bootstrap/cache/*' \
    -C "$APP_DIR" .

log "✓ Current state backed up to: $ROLLBACK_BACKUP_DIR"

# Restore application files
log "Restoring application files..."

# Clear current application (keep .env and storage)
find "$APP_DIR" -mindepth 1 -maxdepth 1 ! -name '.env' ! -name 'storage' -exec rm -rf {} +

# Extract application backup
tar -xzf "$BACKUP_DIR/application.tar.gz" -C "$APP_DIR"
sudo chown -R www-data:www-data "$APP_DIR"

log "✓ Application files restored"

# Restore storage if available
if [ -f "$BACKUP_DIR/storage.tar.gz" ]; then
    log "Restoring storage directory..."
    
    # Backup current storage
    if [ -d "$APP_DIR/storage" ]; then
        mv "$APP_DIR/storage" "$APP_DIR/storage.backup.$(date +%s)"
    fi
    
    # Extract storage backup
    tar -xzf "$BACKUP_DIR/storage.tar.gz" -C "$APP_DIR"
    sudo chown -R www-data:www-data "$APP_DIR/storage"
    
    log "✓ Storage directory restored"
fi

# Set proper permissions
sudo chmod -R 755 "$APP_DIR"
sudo chmod -R 775 "$APP_DIR/storage" 2>/dev/null || true
sudo chmod -R 775 "$APP_DIR/bootstrap/cache" 2>/dev/null || true

# Install Composer dependencies
log "Installing Composer dependencies..."
sudo -u www-data composer install --no-dev --optimize-autoloader --no-interaction

# Restore database if available
if [ -f "$BACKUP_DIR/database.sql.gz" ]; then
    log "Restoring database..."
    
    # Load environment variables
    if [ -f ".env" ]; then
        export $(grep -v '^#' .env | xargs)
    else
        error ".env file not found after restore"
    fi
    
    case "$DB_CONNECTION" in
        "pgsql")
            if command -v psql >/dev/null 2>&1; then
                # Drop and recreate database
                PGPASSWORD="$DB_PASSWORD" dropdb \
                    -h "${DB_HOST:-localhost}" \
                    -p "${DB_PORT:-5432}" \
                    -U "$DB_USERNAME" \
                    --if-exists \
                    "$DB_DATABASE" 2>/dev/null || true
                
                PGPASSWORD="$DB_PASSWORD" createdb \
                    -h "${DB_HOST:-localhost}" \
                    -p "${DB_PORT:-5432}" \
                    -U "$DB_USERNAME" \
                    "$DB_DATABASE"
                
                # Restore database
                gunzip -c "$BACKUP_DIR/database.sql.gz" | \
                PGPASSWORD="$DB_PASSWORD" psql \
                    -h "${DB_HOST:-localhost}" \
                    -p "${DB_PORT:-5432}" \
                    -U "$DB_USERNAME" \
                    -d "$DB_DATABASE" \
                    --quiet
                
                log "✓ PostgreSQL database restored"
            else
                warning "psql not found - skipping database restore"
            fi
            ;;
        "mysql")
            if command -v mysql >/dev/null 2>&1; then
                # Drop and recreate database
                mysql \
                    -h "${DB_HOST:-localhost}" \
                    -P "${DB_PORT:-3306}" \
                    -u "$DB_USERNAME" \
                    -p"$DB_PASSWORD" \
                    -e "DROP DATABASE IF EXISTS $DB_DATABASE; CREATE DATABASE $DB_DATABASE;"
                
                # Restore database
                gunzip -c "$BACKUP_DIR/database.sql.gz" | \
                mysql \
                    -h "${DB_HOST:-localhost}" \
                    -P "${DB_PORT:-3306}" \
                    -u "$DB_USERNAME" \
                    -p"$DB_PASSWORD" \
                    "$DB_DATABASE"
                
                log "✓ MySQL database restored"
            else
                warning "mysql not found - skipping database restore"
            fi
            ;;
        *)
            warning "Unsupported database connection: $DB_CONNECTION"
            ;;
    esac
else
    warning "No database backup found - running migrations"
    sudo -u www-data php artisan migrate --force
fi

# Restore Redis data if available
if [ -f "$BACKUP_DIR/redis.rdb" ]; then
    log "Restoring Redis data..."
    
    if command -v redis-cli >/dev/null 2>&1; then
        # Stop Redis, restore dump file, start Redis
        sudo systemctl stop redis-server 2>/dev/null || sudo service redis-server stop 2>/dev/null || true
        
        REDIS_DIR=$(redis-cli CONFIG GET dir 2>/dev/null | tail -n 1 || echo "/var/lib/redis")
        sudo cp "$BACKUP_DIR/redis.rdb" "$REDIS_DIR/dump.rdb"
        sudo chown redis:redis "$REDIS_DIR/dump.rdb" 2>/dev/null || true
        
        sudo systemctl start redis-server 2>/dev/null || sudo service redis-server start 2>/dev/null || true
        
        log "✓ Redis data restored"
    else
        warning "redis-cli not found - skipping Redis restore"
    fi
fi

# Clear and rebuild caches
log "Rebuilding application caches..."
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan cache:clear

sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo -u www-data php artisan event:cache

# Update storage link
sudo -u www-data php artisan storage:link

# Start services
log "Starting services..."

if command -v supervisorctl >/dev/null 2>&1; then
    # Production environment with Supervisor
    sudo supervisorctl start clearline-reverb:*
    sudo supervisorctl start clearline-horizon:*
    sudo supervisorctl start clearline-worker:*
    log "✓ Supervisor services started"
else
    # WSL environment - start manual processes
    sudo -u www-data nohup php artisan reverb:start --host=0.0.0.0 --port=8080 > storage/logs/reverb.log 2>&1 &
    echo $! > /tmp/reverb.pid
    
    sudo -u www-data nohup php artisan horizon > storage/logs/horizon.log 2>&1 &
    echo $! > /tmp/horizon.pid
    
    log "✓ WSL services started"
fi

# Wait for services to start
sleep 5

# Verify services
log "Verifying services..."

REVERB_PORT=$(grep REVERB_PORT= .env | cut -d'=' -f2)
if [ -z "$REVERB_PORT" ]; then
    REVERB_PORT=8080
fi

if nc -z localhost "$REVERB_PORT" 2>/dev/null; then
    log "✓ WebSocket server is running"
else
    warning "✗ WebSocket server is not responding"
fi

if curl -f -s http://localhost >/dev/null 2>&1; then
    log "✓ Application is responding"
else
    error "✗ Application is not responding"
fi

# Disable maintenance mode
log "Disabling maintenance mode..."
sudo -u www-data php artisan up
log "✓ Application is back online"

echo ""
echo "=== Rollback Summary ==="
echo "Restored from backup: $BACKUP_TIMESTAMP"
echo "Application Directory: $APP_DIR"
echo "Rollback Backup: $ROLLBACK_BACKUP_DIR"
echo "Log File: $LOG_FILE"
echo ""
echo "=== Verification ==="
echo "Application URL: http://$(hostname -I | awk '{print $1}')"
echo "Frontend SPA: http://$(hostname -I | awk '{print $1}')/app"
echo ""

log "Rollback completed successfully!"