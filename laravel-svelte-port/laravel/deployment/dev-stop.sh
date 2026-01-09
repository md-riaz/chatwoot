#!/bin/bash

# ClearLine Development Server Stop Script
# Stops all development servers started by dev-start.sh

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

warning() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] WARNING:${NC} $1"
}

log "Stopping ClearLine development servers..."

# Stop Laravel development server
if [ -f "/tmp/laravel-serve.pid" ]; then
    PID=$(cat /tmp/laravel-serve.pid)
    if kill -0 $PID 2>/dev/null; then
        kill $PID
        log "✓ Laravel development server stopped"
    else
        warning "Laravel development server was not running"
    fi
    rm -f /tmp/laravel-serve.pid
fi

# Stop Laravel Reverb WebSocket server
if [ -f "/tmp/laravel-reverb.pid" ]; then
    PID=$(cat /tmp/laravel-reverb.pid)
    if kill -0 $PID 2>/dev/null; then
        kill $PID
        log "✓ Laravel Reverb WebSocket server stopped"
    else
        warning "Laravel Reverb server was not running"
    fi
    rm -f /tmp/laravel-reverb.pid
fi

# Stop Laravel Horizon queue manager
if [ -f "/tmp/laravel-horizon.pid" ]; then
    PID=$(cat /tmp/laravel-horizon.pid)
    if kill -0 $PID 2>/dev/null; then
        kill $PID
        log "✓ Laravel Horizon queue manager stopped"
    else
        warning "Laravel Horizon was not running"
    fi
    rm -f /tmp/laravel-horizon.pid
fi

# Stop SvelteKit development server
if [ -f "/tmp/svelte-dev.pid" ]; then
    PID=$(cat /tmp/svelte-dev.pid)
    if kill -0 $PID 2>/dev/null; then
        kill $PID
        log "✓ SvelteKit development server stopped"
    else
        warning "SvelteKit development server was not running"
    fi
    rm -f /tmp/svelte-dev.pid
fi

# Kill any remaining processes (cleanup)
pkill -f "php artisan serve" 2>/dev/null && log "✓ Cleaned up remaining Laravel serve processes" || true
pkill -f "php artisan reverb:start" 2>/dev/null && log "✓ Cleaned up remaining Reverb processes" || true
pkill -f "php artisan horizon" 2>/dev/null && log "✓ Cleaned up remaining Horizon processes" || true
pkill -f "vite.*dev" 2>/dev/null && log "✓ Cleaned up remaining Vite processes" || true

# Clean up log files
rm -f /tmp/laravel-serve.log
rm -f /tmp/laravel-reverb.log
rm -f /tmp/laravel-horizon.log
rm -f /tmp/svelte-dev.log
rm -f /tmp/clearline-dev.log

log "🛑 All development servers stopped and cleaned up"

echo ""
echo "Development servers have been stopped."
echo "Run ./deployment/dev-start.sh to start them again."
echo ""