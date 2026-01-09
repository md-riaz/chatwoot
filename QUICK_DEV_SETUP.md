# Quick Development Setup Guide

The `dev-start.sh` script doesn't auto-install dependencies because it was designed to **check** rather than **install** missing requirements. Here's how to set up your development environment manually:

## Why dev-start.sh Failed

The original script only checks for dependencies but doesn't install them:

```bash
# ❌ Only checks, doesn't install
command -v php >/dev/null 2>&1 || error "PHP is not installed"
command -v composer >/dev/null 2>&1 || error "Composer is not installed"
command -v node >/dev/null 2>&1 || error "Node.js is not installed"

# ✅ Only pnpm gets auto-installed
if ! command -v pnpm >/dev/null 2>&1; then
    log "Installing pnpm..."
    curl -fsSL https://get.pnpm.io/install.sh | sh -
fi
```

## Manual Setup (WSL Debian)

### 1. Install PHP and Extensions
```bash
sudo apt update
sudo apt install -y php php-cli php-fpm php-mysql php-pgsql \
    php-sqlite3 php-redis php-curl php-gd php-mbstring \
    php-xml php-zip php-bcmath php-intl php-readline php-dev
```

### 2. Install Composer
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer
```

### 3. Install Node.js and npm
```bash
sudo apt install -y nodejs npm
```

### 4. Install pnpm (optional, script will install this)
```bash
npm install -g pnpm
```

### 5. Install Database and Redis
```bash
# PostgreSQL
sudo apt install -y postgresql postgresql-contrib
sudo systemctl start postgresql
sudo systemctl enable postgresql

# Redis
sudo apt install -y redis-server
sudo systemctl start redis-server
sudo systemctl enable redis-server

# Create PostgreSQL user
sudo -u postgres createuser --superuser $USER
```

### 6. Run the Development Script
```bash
./laravel-svelte-port/laravel/deployment/dev-start.sh
```

## Alternative: Use the New Auto-Setup Script

I created `dev-setup.sh` that automatically installs all dependencies:

```bash
./laravel-svelte-port/laravel/deployment/dev-setup.sh
```

This script will:
- ✅ Auto-install PHP and all extensions
- ✅ Auto-install Composer
- ✅ Auto-install Node.js and npm
- ✅ Auto-install PostgreSQL and Redis
- ✅ Auto-configure development environment
- ✅ Run the original dev-start.sh script

## What Each Script Does

### dev-start.sh (Original)
- ❌ **Checks** for PHP, Composer, Node.js - exits if missing
- ✅ **Installs** pnpm if missing
- ✅ **Installs** Composer dependencies (`composer install`)
- ✅ **Installs** Node.js dependencies (`pnpm install`)
- ✅ **Configures** .env files
- ✅ **Starts** development servers

### dev-setup.sh (New)
- ✅ **Installs** all system dependencies automatically
- ✅ **Configures** PostgreSQL and Redis
- ✅ **Runs** dev-start.sh after setup

## Current Status

Your `dev-setup.sh` script is currently running and installing PHP and dependencies. Once it completes, it will automatically start the development servers.

## Expected Development URLs

Once setup completes:
- **Laravel Backend**: http://localhost:8000
- **Laravel API**: http://localhost:8000/api
- **SvelteKit Frontend**: http://localhost:5173
- **WebSocket**: ws://localhost:8080

## Troubleshooting

If the installation is taking too long, you can:

1. **Cancel** the current installation (Ctrl+C)
2. **Run manual setup** commands above
3. **Try dev-start.sh** again

The issue was that the original script assumed dependencies were already installed, which is a common oversight in development scripts.