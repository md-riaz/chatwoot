#!/bin/bash

# ClearLine Backup Script
# Creates comprehensive backups of application and database

set -e

# Configuration
APP_DIR="/var/www/clearline"
BACKUP_BASE_DIR="/var/backups/clearline"
LOG_FILE="/var/log/clearline-backup.log"
RETENTION_DAYS=30

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
    exit 1
}

warning() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] WARNING:${NC} $1" | tee -a "$LOG_FILE"
}

# Check if running as root or with sudo
if [[ $EUID -ne 0 ]]; then
   error "This script must be run as root or with sudo"
fi

log "Starting ClearLine backup process..."

# Create backup directory structure
TIMESTAMP=$(date +%Y%m%d-%H%M%S)
BACKUP_DIR="$BACKUP_BASE_DIR/$TIMESTAMP"
mkdir -p "$BACKUP_DIR"

# Check if application exists
if [ ! -d "$APP_DIR" ]; then
    error "Application directory not found: $APP_DIR"
fi

cd "$APP_DIR"

# Load environment variables
if [ -f ".env" ]; then
    export $(grep -v '^#' .env | xargs)
else
    error ".env file not found"
fi

log "Creating application backup..."

# Backup application files (excluding vendor and node_modules)
tar -czf "$BACKUP_DIR/application.tar.gz" \
    --exclude='vendor' \
    --exclude='node_modules' \
    --exclude='storage/logs/*' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='bootstrap/cache/*' \
    --exclude='.git' \
    -C "$APP_DIR" .

log "✓ Application files backed up"

# Backup storage directory separately (includes uploads, logs)
if [ -d "$APP_DIR/storage" ]; then
    tar -czf "$BACKUP_DIR/storage.tar.gz" -C "$APP_DIR" storage/
    log "✓ Storage directory backed up"
fi

# Backup database
log "Creating database backup..."

if [ -n "$DB_DATABASE" ]; then
    case "$DB_CONNECTION" in
        "pgsql")
            if command -v pg_dump >/dev/null 2>&1; then
                PGPASSWORD="$DB_PASSWORD" pg_dump \
                    -h "${DB_HOST:-localhost}" \
                    -p "${DB_PORT:-5432}" \
                    -U "$DB_USERNAME" \
                    -d "$DB_DATABASE" \
                    --no-password \
                    --verbose \
                    --clean \
                    --if-exists \
                    --create \
                    > "$BACKUP_DIR/database.sql"
                
                # Compress database backup
                gzip "$BACKUP_DIR/database.sql"
                log "✓ PostgreSQL database backed up"
            else
                warning "pg_dump not found - skipping database backup"
            fi
            ;;
        "mysql")
            if command -v mysqldump >/dev/null 2>&1; then
                mysqldump \
                    -h "${DB_HOST:-localhost}" \
                    -P "${DB_PORT:-3306}" \
                    -u "$DB_USERNAME" \
                    -p"$DB_PASSWORD" \
                    --single-transaction \
                    --routines \
                    --triggers \
                    --add-drop-database \
                    --create-options \
                    "$DB_DATABASE" \
                    > "$BACKUP_DIR/database.sql"
                
                # Compress database backup
                gzip "$BACKUP_DIR/database.sql"
                log "✓ MySQL database backed up"
            else
                warning "mysqldump not found - skipping database backup"
            fi
            ;;
        *)
            warning "Unsupported database connection: $DB_CONNECTION"
            ;;
    esac
else
    warning "No database configuration found"
fi

# Backup Redis data (if Redis is configured)
if [ -n "$REDIS_HOST" ] && [ "$REDIS_HOST" != "null" ]; then
    if command -v redis-cli >/dev/null 2>&1; then
        log "Creating Redis backup..."
        
        # Get Redis data directory
        REDIS_DIR=$(redis-cli CONFIG GET dir | tail -n 1)
        REDIS_DBFILENAME=$(redis-cli CONFIG GET dbfilename | tail -n 1)
        
        if [ -f "$REDIS_DIR/$REDIS_DBFILENAME" ]; then
            # Force Redis to save current state
            redis-cli BGSAVE
            
            # Wait for background save to complete
            while [ "$(redis-cli LASTSAVE)" = "$(redis-cli LASTSAVE)" ]; do
                sleep 1
            done
            
            # Copy Redis dump file
            cp "$REDIS_DIR/$REDIS_DBFILENAME" "$BACKUP_DIR/redis.rdb"
            log "✓ Redis data backed up"
        else
            warning "Redis dump file not found"
        fi
    else
        warning "redis-cli not found - skipping Redis backup"
    fi
fi

# Backup configuration files
log "Backing up configuration files..."

CONFIG_BACKUP_DIR="$BACKUP_DIR/config"
mkdir -p "$CONFIG_BACKUP_DIR"

# Nginx configuration
if [ -f "/etc/nginx/sites-available/clearline" ]; then
    cp "/etc/nginx/sites-available/clearline" "$CONFIG_BACKUP_DIR/nginx-clearline.conf"
fi

if [ -f "/etc/nginx/sites-available/clearline-dev" ]; then
    cp "/etc/nginx/sites-available/clearline-dev" "$CONFIG_BACKUP_DIR/nginx-clearline-dev.conf"
fi

# Supervisor configurations
if [ -d "/etc/supervisor/conf.d" ]; then
    cp /etc/supervisor/conf.d/clearline-*.conf "$CONFIG_BACKUP_DIR/" 2>/dev/null || true
fi

# PHP-FPM configuration
if [ -f "/etc/php/8.2/fpm/pool.d/www.conf" ]; then
    cp "/etc/php/8.2/fpm/pool.d/www.conf" "$CONFIG_BACKUP_DIR/php-fpm-www.conf"
fi

log "✓ Configuration files backed up"

# Create backup manifest
log "Creating backup manifest..."

cat > "$BACKUP_DIR/manifest.txt" << EOF
ClearLine Backup Manifest
========================
Backup Date: $(date)
Backup Directory: $BACKUP_DIR
Application Directory: $APP_DIR

Files Included:
- application.tar.gz: Application code (excluding vendor, node_modules)
- storage.tar.gz: Storage directory (uploads, logs)
- database.sql.gz: Database dump
- redis.rdb: Redis data (if available)
- config/: Configuration files

Environment:
- PHP Version: $(php --version | head -n 1)
- Laravel Version: $(cd "$APP_DIR" && php artisan --version)
- Database: $DB_CONNECTION ($DB_DATABASE)
- Redis: ${REDIS_HOST:-not configured}

System Info:
- Hostname: $(hostname)
- OS: $(lsb_release -d 2>/dev/null | cut -f2 || uname -a)
- Disk Usage: $(df -h "$APP_DIR" | awk 'NR==2 {print $5}')
- Memory Usage: $(free -h | awk 'NR==2{printf "%s/%s (%.0f%%)", $3,$2,$3*100/$2}')

Backup Size: $(du -sh "$BACKUP_DIR" | cut -f1)
EOF

log "✓ Backup manifest created"

# Calculate backup size
BACKUP_SIZE=$(du -sh "$BACKUP_DIR" | cut -f1)
log "Backup completed - Total size: $BACKUP_SIZE"

# Clean up old backups
log "Cleaning up old backups (keeping last $RETENTION_DAYS days)..."

find "$BACKUP_BASE_DIR" -type d -name "20*" -mtime +$RETENTION_DAYS -exec rm -rf {} + 2>/dev/null || true

REMAINING_BACKUPS=$(find "$BACKUP_BASE_DIR" -type d -name "20*" | wc -l)
log "✓ Cleanup completed - $REMAINING_BACKUPS backups remaining"

# Create latest symlink
ln -sfn "$BACKUP_DIR" "$BACKUP_BASE_DIR/latest"

echo ""
echo "=== Backup Summary ==="
echo "Backup Location: $BACKUP_DIR"
echo "Backup Size: $BACKUP_SIZE"
echo "Manifest: $BACKUP_DIR/manifest.txt"
echo "Latest Backup: $BACKUP_BASE_DIR/latest"
echo "Log File: $LOG_FILE"
echo ""
echo "=== Restore Instructions ==="
echo "1. Extract application: tar -xzf $BACKUP_DIR/application.tar.gz"
echo "2. Extract storage: tar -xzf $BACKUP_DIR/storage.tar.gz"
echo "3. Restore database: gunzip -c $BACKUP_DIR/database.sql.gz | psql/mysql"
echo "4. Restore configurations from $BACKUP_DIR/config/"
echo ""

log "Backup process completed successfully"