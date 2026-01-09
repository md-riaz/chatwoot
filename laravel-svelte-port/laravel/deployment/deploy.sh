#!/bin/bash

# ClearLine Deployment Script
# Deploys Laravel application with Reverb WebSocket support

set -e

# Configuration
APP_DIR="/var/www/clearline"
NGINX_CONFIG="/etc/nginx/sites-available/clearline"
SUPERVISOR_DIR="/etc/supervisor/conf.d"
BACKUP_DIR="/var/backups/clearline"
LOG_FILE="/var/log/clearline-deploy.log"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Logging function
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

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   error "This script should not be run as root for security reasons"
fi

# Check if required commands exist
command -v php >/dev/null 2>&1 || error "PHP is not installed"
command -v composer >/dev/null 2>&1 || error "Composer is not installed"
command -v nginx >/dev/null 2>&1 || error "Nginx is not installed"
command -v supervisorctl >/dev/null 2>&1 || error "Supervisor is not installed"

log "Starting ClearLine deployment..."

# Create backup directory
sudo mkdir -p "$BACKUP_DIR"

# Backup current deployment if exists
if [ -d "$APP_DIR" ]; then
    log "Creating backup of current deployment..."
    sudo tar -czf "$BACKUP_DIR/clearline-backup-$(date +%Y%m%d-%H%M%S).tar.gz" -C "$APP_DIR" .
fi

# Create application directory
sudo mkdir -p "$APP_DIR"
sudo chown -R www-data:www-data "$APP_DIR"

log "Deploying application code..."

# Copy application files (assuming current directory is the source)
sudo cp -r . "$APP_DIR/"
sudo chown -R www-data:www-data "$APP_DIR"

# Set proper permissions
sudo chmod -R 755 "$APP_DIR"
sudo chmod -R 775 "$APP_DIR/storage"
sudo chmod -R 775 "$APP_DIR/bootstrap/cache"

log "Installing Composer dependencies..."
cd "$APP_DIR"
sudo -u www-data composer install --no-dev --optimize-autoloader --no-interaction

# Check if .env exists
if [ ! -f "$APP_DIR/.env" ]; then
    if [ -f "$APP_DIR/.env.example" ]; then
        log "Creating .env from .env.example..."
        sudo -u www-data cp "$APP_DIR/.env.example" "$APP_DIR/.env"
        warning "Please configure .env file before continuing"
        read -p "Press enter to continue after configuring .env..."
    else
        error ".env file not found and .env.example doesn't exist"
    fi
fi

# Generate application key if not set
if ! grep -q "APP_KEY=base64:" "$APP_DIR/.env"; then
    log "Generating application key..."
    sudo -u www-data php artisan key:generate --force
fi

# Generate Reverb credentials if not set
if ! grep -q "REVERB_APP_KEY=" "$APP_DIR/.env" || [ -z "$(grep REVERB_APP_KEY= $APP_DIR/.env | cut -d'=' -f2)" ]; then
    log "Generating Reverb credentials..."
    
    # Generate unique credentials
    REVERB_APP_ID=$(openssl rand -hex 6)
    REVERB_APP_KEY=$(openssl rand -base64 32 | tr -d "=+/" | cut -c1-25)
    REVERB_APP_SECRET=$(openssl rand -base64 32 | tr -d "=+/" | cut -c1-25)
    
    # Update .env file
    sudo -u www-data sed -i "s/REVERB_APP_ID=.*/REVERB_APP_ID=$REVERB_APP_ID/" "$APP_DIR/.env"
    sudo -u www-data sed -i "s/REVERB_APP_KEY=.*/REVERB_APP_KEY=$REVERB_APP_KEY/" "$APP_DIR/.env"
    sudo -u www-data sed -i "s/REVERB_APP_SECRET=.*/REVERB_APP_SECRET=$REVERB_APP_SECRET/" "$APP_DIR/.env"
    
    log "Generated Reverb credentials: APP_ID=$REVERB_APP_ID, APP_KEY=$REVERB_APP_KEY"
fi

log "Running database migrations..."
sudo -u www-data php artisan migrate --force

log "Optimizing application..."
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo -u www-data php artisan event:cache

# Create storage link
sudo -u www-data php artisan storage:link

log "Configuring Nginx..."
if [ -f "deployment/nginx/clearline.conf" ]; then
    # Update the Nginx configuration with the actual Reverb app key
    REVERB_APP_KEY=$(grep REVERB_APP_KEY= "$APP_DIR/.env" | cut -d'=' -f2)
    if [ -n "$REVERB_APP_KEY" ]; then
        # Replace the default app key in Nginx config
        sudo sed "s/clearline-app-key/$REVERB_APP_KEY/g" "deployment/nginx/clearline.conf" > /tmp/clearline.conf
        sudo mv /tmp/clearline.conf "$NGINX_CONFIG"
    else
        sudo cp "deployment/nginx/clearline.conf" "$NGINX_CONFIG"
    fi
    
    sudo nginx -t || error "Nginx configuration test failed"
    sudo systemctl reload nginx
    log "Nginx configuration updated with Reverb app key: $REVERB_APP_KEY"
else
    warning "Nginx configuration file not found at deployment/nginx/clearline.conf"
fi

log "Configuring Supervisor..."

# Copy supervisor configurations
if [ -d "deployment/supervisor" ]; then
    sudo cp deployment/supervisor/*.conf "$SUPERVISOR_DIR/"
    
    # Update supervisor
    sudo supervisorctl reread
    sudo supervisorctl update
    
    # Start services
    sudo supervisorctl start clearline-reverb:*
    sudo supervisorctl start clearline-horizon:*
    sudo supervisorctl start clearline-worker:*
    
    log "Supervisor services started"
else
    warning "Supervisor configuration directory not found at deployment/supervisor/"
fi

# Verify services are running
log "Verifying services..."

# Check Nginx
if sudo systemctl is-active --quiet nginx; then
    log "✓ Nginx is running"
else
    error "✗ Nginx is not running"
fi

# Check Supervisor services
if sudo supervisorctl status clearline-reverb:* | grep -q RUNNING; then
    log "✓ Reverb WebSocket server is running"
else
    warning "✗ Reverb WebSocket server is not running"
fi

if sudo supervisorctl status clearline-horizon:* | grep -q RUNNING; then
    log "✓ Horizon queue manager is running"
else
    warning "✗ Horizon queue manager is not running"
fi

if sudo supervisorctl status clearline-worker:* | grep -q RUNNING; then
    log "✓ Queue workers are running"
else
    warning "✗ Queue workers are not running"
fi

# Test WebSocket connection
REVERB_PORT=$(grep REVERB_PORT= "$APP_DIR/.env" | cut -d'=' -f2)
if [ -z "$REVERB_PORT" ]; then
    REVERB_PORT=8080
fi

if nc -z localhost "$REVERB_PORT"; then
    log "✓ WebSocket server is listening on port $REVERB_PORT"
else
    warning "✗ WebSocket server is not listening on port $REVERB_PORT"
fi

# Test application
if curl -f -s http://localhost >/dev/null; then
    log "✓ Application is responding"
else
    warning "✗ Application is not responding"
fi

log "Deployment completed successfully!"

# Note about frontend builds
echo ""
echo "=== Frontend Build Notice ==="
echo "Frontend assets are NOT built automatically during deployment."
echo "Run the frontend build separately:"
echo "  ./deployment/build-frontend.sh"
echo "This allows for independent frontend deployments and better CI/CD practices."
echo ""

# Display important information
echo ""
echo "=== Deployment Summary ==="
echo "Application Directory: $APP_DIR"
echo "Nginx Configuration: $NGINX_CONFIG"
echo "WebSocket Port: $REVERB_PORT"
echo "Log File: $LOG_FILE"
echo ""
echo "=== Next Steps ==="
echo "1. Build and deploy frontend: ./deployment/build-frontend.sh"
echo "2. Configure your domain DNS to point to this server"
echo "3. Update CORS_ALLOWED_ORIGINS in .env with your frontend domain"
echo "4. Configure SSL certificates for production"
echo "5. Update frontend VITE_WS_URL with your domain and Reverb app key"
echo "6. Test WebSocket connection from frontend"
echo ""
echo "=== Useful Commands ==="
echo "View logs: tail -f $LOG_FILE"
echo "Check services: sudo supervisorctl status"
echo "Restart Reverb: sudo supervisorctl restart clearline-reverb:*"
echo "View Reverb logs: tail -f $APP_DIR/storage/logs/reverb.log"
echo ""

log "Deployment script finished"