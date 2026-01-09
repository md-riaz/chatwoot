#!/bin/bash

# ClearLine Development Server Script
# Starts Laravel + SvelteKit development servers locally in WSL
# Files remain in current directory for live development

set -e

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
LARAVEL_DIR="$(dirname "$SCRIPT_DIR")"
SVELTE_DIR="$(dirname "$LARAVEL_DIR")/svelte-ui"
LOG_FILE="/tmp/clearline-dev.log"

# Colors for output
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

# Cleanup function for graceful shutdown
cleanup() {
    log "Shutting down development servers..."
    
    # Kill Laravel processes
    if [ -f "/tmp/laravel-serve.pid" ]; then
        kill $(cat /tmp/laravel-serve.pid) 2>/dev/null || true
        rm -f /tmp/laravel-serve.pid
    fi
    
    if [ -f "/tmp/laravel-reverb.pid" ]; then
        kill $(cat /tmp/laravel-reverb.pid) 2>/dev/null || true
        rm -f /tmp/laravel-reverb.pid
    fi
    
    if [ -f "/tmp/laravel-horizon.pid" ]; then
        kill $(cat /tmp/laravel-horizon.pid) 2>/dev/null || true
        rm -f /tmp/laravel-horizon.pid
    fi
    
    # Kill SvelteKit process
    if [ -f "/tmp/svelte-dev.pid" ]; then
        kill $(cat /tmp/svelte-dev.pid) 2>/dev/null || true
        rm -f /tmp/svelte-dev.pid
    fi
    
    # Kill any remaining processes
    pkill -f "php artisan serve" 2>/dev/null || true
    pkill -f "php artisan reverb:start" 2>/dev/null || true
    pkill -f "php artisan horizon" 2>/dev/null || true
    pkill -f "vite.*dev" 2>/dev/null || true
    
    log "Development servers stopped"
    exit 0
}

# Set up signal handlers
trap cleanup SIGINT SIGTERM

log "Starting ClearLine development environment..."

# Check WSL environment
if ! grep -q Microsoft /proc/version; then
    warning "This script is designed for WSL. You may encounter issues on other systems."
fi

# Check if directories exist
if [ ! -d "$LARAVEL_DIR" ]; then
    error "Laravel directory not found: $LARAVEL_DIR"
fi

if [ ! -d "$SVELTE_DIR" ]; then
    error "SvelteKit directory not found: $SVELTE_DIR"
fi

# Check required commands
command -v php >/dev/null 2>&1 || error "PHP is not installed"
command -v composer >/dev/null 2>&1 || error "Composer is not installed"
command -v node >/dev/null 2>&1 || error "Node.js is not installed"

# Install pnpm if not available
if ! command -v pnpm >/dev/null 2>&1; then
    log "Installing pnpm..."
    curl -fsSL https://get.pnpm.io/install.sh | sh -
    export PATH="$HOME/.local/share/pnpm:$PATH"
    
    # Fallback to npm if pnpm installation fails
    if ! command -v pnpm >/dev/null 2>&1; then
        log "pnpm installation failed, using npm to install pnpm..."
        npm install -g pnpm 2>/dev/null || warning "Could not install pnpm globally"
    fi
fi

# Setup Laravel development environment
log "Setting up Laravel development environment..."
cd "$LARAVEL_DIR"

# Install Composer dependencies
if [ ! -d "vendor" ] || [ "composer.json" -nt "vendor/autoload.php" ]; then
    log "Installing/updating Composer dependencies..."
    composer install
fi

# Setup .env for development
if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        log "Creating .env from .env.example..."
        cp ".env.example" ".env"
        
        # Generate application key
        php artisan key:generate
        
        # Set development-specific values
        sed -i "s/APP_ENV=.*/APP_ENV=local/" .env
        sed -i "s/APP_DEBUG=.*/APP_DEBUG=true/" .env
        sed -i "s/APP_URL=.*/APP_URL=http:\/\/localhost:8000/" .env
        sed -i "s/DB_DATABASE=.*/DB_DATABASE=clearline_development/" .env
        sed -i "s/DB_USERNAME=.*/DB_USERNAME=postgres/" .env
        sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=/" .env
        
        # CORS settings for development
        sed -i "s/CORS_ALLOWED_ORIGINS=.*/CORS_ALLOWED_ORIGINS=http:\/\/localhost:5173,http:\/\/localhost:3000/" .env
        
        # Session and cache settings for development
        sed -i "s/SESSION_DRIVER=.*/SESSION_DRIVER=file/" .env
        sed -i "s/CACHE_DRIVER=.*/CACHE_DRIVER=file/" .env
        sed -i "s/QUEUE_CONNECTION=.*/QUEUE_CONNECTION=redis/" .env
        
        # Generate static Reverb credentials for development (consistent across runs)
        REVERB_APP_ID="clearline-dev"
        REVERB_APP_KEY="clearline-dev-key-12345"
        REVERB_APP_SECRET="clearline-dev-secret-67890"
        
        sed -i "s/REVERB_APP_ID=.*/REVERB_APP_ID=$REVERB_APP_ID/" .env
        sed -i "s/REVERB_APP_KEY=.*/REVERB_APP_KEY=$REVERB_APP_KEY/" .env
        sed -i "s/REVERB_APP_SECRET=.*/REVERB_APP_SECRET=$REVERB_APP_SECRET/" .env
        sed -i "s/REVERB_HOST=.*/REVERB_HOST=localhost/" .env
        sed -i "s/REVERB_PORT=.*/REVERB_PORT=8080/" .env
        sed -i "s/REVERB_SCHEME=.*/REVERB_SCHEME=http/" .env
        
        log "Generated development .env file"
    else
        error ".env.example not found"
    fi
fi

# Ensure database exists
log "Setting up development database..."
if command -v createdb >/dev/null 2>&1; then
    createdb clearline_development 2>/dev/null || log "Database already exists or could not create"
else
    warning "createdb not found - ensure PostgreSQL database 'clearline_development' exists"
fi

# Run migrations
log "Running database migrations..."
php artisan migrate

# Create storage link
php artisan storage:link 2>/dev/null || true

# Clear caches for development
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Setup SvelteKit development environment
log "Setting up SvelteKit development environment..."
cd "$SVELTE_DIR"

# Create/update SvelteKit .env file with matching credentials
log "Creating/updating SvelteKit .env file..."
cat > .env << EOF
# SvelteKit Development Environment
# Auto-generated by dev-start.sh - matches Laravel backend

# API Configuration
VITE_API_BASE_URL=http://localhost:8000

# WebSocket Configuration (matches Laravel Reverb)
VITE_WS_URL=ws://localhost:8080
VITE_REVERB_APP_KEY=clearline-dev-key-12345
VITE_REVERB_HOST=localhost
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME=ws

# Development Settings
NODE_ENV=development
VITE_APP_ENV=development
VITE_APP_DEBUG=true

# CORS Settings
VITE_CORS_ENABLED=true
EOF

log "✓ SvelteKit .env file created with matching Reverb credentials"

# Install Node.js dependencies
if [ ! -d "node_modules" ] || [ "package.json" -nt "node_modules/.package-lock.json" ]; then
    log "Installing/updating Node.js dependencies..."
    if command -v pnpm >/dev/null 2>&1; then
        pnpm install
    else
        npm install
    fi
fi

# Start development servers
log "Starting development servers..."

# Start Laravel development server
cd "$LARAVEL_DIR"
log "Starting Laravel development server on http://localhost:8000..."
nohup php artisan serve --host=0.0.0.0 --port=8000 > /tmp/laravel-serve.log 2>&1 &
echo $! > /tmp/laravel-serve.pid

# Start Laravel Reverb WebSocket server
log "Starting Laravel Reverb WebSocket server on port 8080..."
nohup php artisan reverb:start --host=0.0.0.0 --port=8080 > /tmp/laravel-reverb.log 2>&1 &
echo $! > /tmp/laravel-reverb.pid

# Start Laravel Horizon (queue manager)
log "Starting Laravel Horizon queue manager..."
nohup php artisan horizon > /tmp/laravel-horizon.log 2>&1 &
echo $! > /tmp/laravel-horizon.pid

# Start SvelteKit development server
cd "$SVELTE_DIR"
log "Starting SvelteKit development server on http://localhost:5173..."
if command -v pnpm >/dev/null 2>&1; then
    nohup pnpm run dev --host 0.0.0.0 --port 5173 > /tmp/svelte-dev.log 2>&1 &
else
    nohup npm run dev -- --host 0.0.0.0 --port 5173 > /tmp/svelte-dev.log 2>&1 &
fi
echo $! > /tmp/svelte-dev.pid

# Wait for servers to start
log "Waiting for servers to start..."
sleep 5

# Verify servers are running
log "Verifying development servers..."

# Check Laravel server
if curl -f -s http://localhost:8000 >/dev/null 2>&1; then
    log "✓ Laravel server is running on http://localhost:8000"
else
    warning "✗ Laravel server is not responding"
fi

# Check WebSocket server
if nc -z localhost 8080 2>/dev/null; then
    log "✓ WebSocket server is running on ws://localhost:8080"
else
    warning "✗ WebSocket server is not responding"
fi

# Check SvelteKit server (may take longer to start)
for i in {1..10}; do
    if curl -f -s http://localhost:5173 >/dev/null 2>&1; then
        log "✓ SvelteKit server is running on http://localhost:5173"
        break
    elif [ $i -eq 10 ]; then
        warning "✗ SvelteKit server is not responding after 10 attempts"
    else
        sleep 2
    fi
done

# Check Horizon
if pgrep -f "artisan horizon" >/dev/null; then
    log "✓ Horizon queue manager is running"
else
    warning "✗ Horizon queue manager is not running"
fi

echo ""
echo "🚀 Development Environment Ready!"
echo ""
echo "=== Development URLs ==="
echo "Laravel Backend:     http://localhost:8000"
echo "Laravel API:         http://localhost:8000/api"
echo "SvelteKit Frontend:  http://localhost:5173"
echo "WebSocket:           ws://localhost:8080"
echo ""
echo "=== Development Features ==="
echo "✓ Live reload enabled for both frontend and backend"
echo "✓ Hot module replacement (HMR) for SvelteKit"
echo "✓ Laravel file watching (manual refresh needed)"
echo "✓ WebSocket server for real-time features"
echo "✓ Queue processing with Horizon"
echo "✓ Static Reverb credentials for consistent development"
echo ""
echo "=== WebSocket Configuration ==="
echo "Reverb App ID:    clearline-dev"
echo "Reverb App Key:   clearline-dev-key-12345"
echo "WebSocket URL:    ws://localhost:8080"
echo "Frontend Config:  Auto-configured in SvelteKit .env"
echo ""
echo "=== File Locations ==="
echo "Laravel:   $LARAVEL_DIR"
echo "SvelteKit: $SVELTE_DIR"
echo "Logs:      /tmp/clearline-dev.log"
echo ""
echo "=== Development Workflow ==="
echo "1. Edit Laravel files - changes apply immediately"
echo "2. Edit SvelteKit files - browser auto-refreshes"
echo "3. API changes available at http://localhost:8000/api"
echo "4. Frontend proxies API calls to Laravel backend"
echo ""
echo "=== Useful Commands ==="
echo "View Laravel logs:    tail -f /tmp/laravel-serve.log"
echo "View SvelteKit logs:  tail -f /tmp/svelte-dev.log"
echo "View WebSocket logs:  tail -f /tmp/laravel-reverb.log"
echo "View Horizon logs:    tail -f /tmp/laravel-horizon.log"
echo "Laravel Tinker:       cd $LARAVEL_DIR && php artisan tinker"
echo "Run migrations:       cd $LARAVEL_DIR && php artisan migrate"
echo ""
echo "Press Ctrl+C to stop all development servers"
echo ""

# Keep script running and monitor servers
while true; do
    # Check if any server died and restart if needed
    if [ -f "/tmp/laravel-serve.pid" ] && ! kill -0 $(cat /tmp/laravel-serve.pid) 2>/dev/null; then
        warning "Laravel server died, restarting..."
        cd "$LARAVEL_DIR"
        nohup php artisan serve --host=0.0.0.0 --port=8000 > /tmp/laravel-serve.log 2>&1 &
        echo $! > /tmp/laravel-serve.pid
    fi
    
    if [ -f "/tmp/laravel-reverb.pid" ] && ! kill -0 $(cat /tmp/laravel-reverb.pid) 2>/dev/null; then
        warning "Reverb server died, restarting..."
        cd "$LARAVEL_DIR"
        nohup php artisan reverb:start --host=0.0.0.0 --port=8080 > /tmp/laravel-reverb.log 2>&1 &
        echo $! > /tmp/laravel-reverb.pid
    fi
    
    if [ -f "/tmp/laravel-horizon.pid" ] && ! kill -0 $(cat /tmp/laravel-horizon.pid) 2>/dev/null; then
        warning "Horizon died, restarting..."
        cd "$LARAVEL_DIR"
        nohup php artisan horizon > /tmp/laravel-horizon.log 2>&1 &
        echo $! > /tmp/laravel-horizon.pid
    fi
    
    if [ -f "/tmp/svelte-dev.pid" ] && ! kill -0 $(cat /tmp/svelte-dev.pid) 2>/dev/null; then
        warning "SvelteKit server died, restarting..."
        cd "$SVELTE_DIR"
        if command -v pnpm >/dev/null 2>&1; then
            nohup pnpm run dev --host 0.0.0.0 --port 5173 > /tmp/svelte-dev.log 2>&1 &
        else
            nohup npm run dev -- --host 0.0.0.0 --port 5173 > /tmp/svelte-dev.log 2>&1 &
        fi
        echo $! > /tmp/svelte-dev.pid
    fi
    
    sleep 10
done