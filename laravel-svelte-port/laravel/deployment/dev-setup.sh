#!/bin/bash

# ClearLine Development Environment Setup Script
# Automatically installs all required dependencies and starts development servers
# Designed for WSL Debian/Ubuntu environments

set -e

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
LARAVEL_DIR="$(dirname "$SCRIPT_DIR")"
SVELTE_DIR="$(dirname "$LARAVEL_DIR")/svelte-ui"
LOG_FILE="/tmp/clearline-setup.log"

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

# --- GLOBAL FIX FOR WSL SLEEP ISSUE ---
# Create a local bin directory for our shim
SHIM_DIR="$SCRIPT_DIR/.bin"
mkdir -p "$SHIM_DIR"

# Create a 'sleep' shim that doesn't crash on WSL clock errors
cat << 'EOF' > "$SHIM_DIR/sleep"
#!/bin/bash
# This shim avoids the "cannot read realtime clock: Invalid argument" error in WSL
/bin/sleep "$@" 2>/dev/null || {
    # If /bin/sleep fails, use perl or read to wait
    D=$1
    # Remove any 's' suffix if present
    D=${D%s}
    perl -e "select(undef, undef, undef, $D)" 2>/dev/null || read -t "$D" -n 1 2>/dev/null || true
}
EOF
chmod +x "$SHIM_DIR/sleep"

# Add it to the FRONT of the PATH so all commands (including sudo and system scripts) use it
export PATH="$SHIM_DIR:$PATH"
# --------------------------------------

log "🚀 Setting up ClearLine development environment..."

# Check WSL environment
if ! grep -q Microsoft /proc/version; then
    warning "This script is designed for WSL. You may encounter issues on other systems."
fi

# Update package lists
log "Updating package lists..."
sudo apt update

# Install PHP and required extensions (Debian)
if ! command -v php >/dev/null 2>&1; then
    log "Installing PHP and extensions..."
    sudo apt install -y php php-cli php-fpm php-mysql php-pgsql \
        php-sqlite3 php-redis php-curl php-gd php-mbstring \
        php-xml php-zip php-bcmath php-intl php-readline \
        php-dev php-common php-json php-opcache
else
    log "✓ PHP is already installed: $(php --version | head -n1)"
fi

# Install Composer
if ! command -v composer >/dev/null 2>&1; then
    log "Installing Composer..."
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    sudo chmod +x /usr/local/bin/composer
else
    log "✓ Composer is already installed: $(composer --version | head -n1)"
fi

# Install Node.js (Debian) - Fix architecture issues
if ! command -v node >/dev/null 2>&1 || ! node --version >/dev/null 2>&1; then
    log "Installing/fixing Node.js..."
    # Remove potentially corrupted Node.js
    sudo apt remove -y nodejs npm 2>/dev/null || true
    
    # Install Node.js from NodeSource repository for correct architecture
    curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
    sudo apt install -y nodejs
    
    # Verify installation
    if node --version >/dev/null 2>&1; then
        log "✓ Node.js installed successfully: $(node --version)"
    else
        warning "Node.js installation may have issues"
    fi
else
    log "✓ Node.js is already installed: $(node --version)"
fi

# Verify npm is installed
if ! command -v npm >/dev/null 2>&1; then
    warning "npm not found. nodejs installation may have failed to include it."
else
    log "✓ npm is already installed: $(npm --version)"
fi

# Install PostgreSQL (WSL compatible)
if ! command -v psql >/dev/null 2>&1; then
    log "Installing PostgreSQL..."
    sudo apt install -y postgresql postgresql-contrib
    
    # Start PostgreSQL manually in WSL (no systemd)
    if grep -q Microsoft /proc/version; then
        log "Starting PostgreSQL manually (WSL environment)..."
        # We use 'PATH=$PATH' to ensure our shim is preserved inside sudo
        sudo env "PATH=$PATH" service postgresql start || sudo env "PATH=$PATH" pg_ctlcluster 17 main start
        sleep 2
    else
        sudo systemctl start postgresql
        sudo systemctl enable postgresql
    fi
    
    # Create development user
    sudo -u postgres createuser --superuser $USER 2>/dev/null || log "PostgreSQL user already exists"
else
    log "✓ PostgreSQL is already installed"
    # Ensure PostgreSQL is running
    if grep -q Microsoft /proc/version; then
        sudo env "PATH=$PATH" service postgresql start 2>/dev/null || sudo env "PATH=$PATH" pg_ctlcluster 17 main start 2>/dev/null || true
        sleep 1
    fi
fi

# Install Redis (WSL compatible)
if ! command -v redis-cli >/dev/null 2>&1; then
    log "Installing Redis..."
    sudo apt install -y redis-server
    
    # Start Redis manually in WSL (no systemd)
    if grep -q Microsoft /proc/version; then
        log "Starting Redis manually (WSL environment)..."
        sudo env "PATH=$PATH" service redis-server start
        sleep 2
    else
        sudo systemctl start redis-server
        sudo systemctl enable redis-server
    fi
else
    log "✓ Redis is already installed"
    # Ensure Redis is running
    if grep -q Microsoft /proc/version; then
        sudo env "PATH=$PATH" service redis-server start 2>/dev/null || true
        sleep 1
    fi
fi

# Install additional development tools
log "Installing additional development tools..."
sudo apt install -y git curl wget unzip zip netcat-openbsd

log "✅ All dependencies installed successfully!"

# Now run the original dev-start.sh script
log "Starting development servers..."
exec "$SCRIPT_DIR/dev-start.sh"