#!/bin/bash
set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "=================================="
echo "Starting deployment..."
echo "=================================="

# Function to print colored messages
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_info() {
    echo -e "ℹ $1"
}

# Function to check command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Function to compare versions
version_ge() {
    [ "$(printf '%s\n' "$1" "$2" | sort -V | head -n1)" = "$2" ]
}

# ====================================
# Environment Setup & Verification
# ====================================

echo ""
echo "Checking system requirements..."
echo "=================================="

# Check PHP version
print_info "Checking PHP version..."
if ! command_exists php; then
    print_error "PHP is not installed"
    echo "Please install PHP 8.2 or higher:"
    echo "  Ubuntu/Debian: sudo apt-get install php8.2-cli php8.2-fpm php8.2-pgsql php8.2-redis php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-bcmath php8.2-intl"
    echo "  CentOS/RHEL: sudo yum install php82 php82-cli php82-fpm php82-pgsql php82-redis php82-mbstring php82-xml php82-curl php82-zip php82-bcmath php82-intl"
    exit 1
fi

PHP_VERSION=$(php -r 'echo PHP_VERSION;')
REQUIRED_PHP_VERSION="8.2.0"

if version_ge "$PHP_VERSION" "$REQUIRED_PHP_VERSION"; then
    print_success "PHP $PHP_VERSION (>= $REQUIRED_PHP_VERSION required)"
else
    print_error "PHP version $PHP_VERSION is below required version $REQUIRED_PHP_VERSION"
    echo "Please upgrade PHP to version 8.2 or higher"
    exit 1
fi

# Check required PHP extensions
print_info "Checking required PHP extensions..."
REQUIRED_EXTENSIONS=("pdo" "pdo_pgsql" "pgsql" "redis" "mbstring" "xml" "curl" "zip" "bcmath" "intl" "ctype" "fileinfo" "json" "tokenizer")
MISSING_EXTENSIONS=()

for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if ! php -m | grep -qi "^$ext$"; then
        MISSING_EXTENSIONS+=("$ext")
    fi
done

if [ ${#MISSING_EXTENSIONS[@]} -eq 0 ]; then
    print_success "All required PHP extensions are installed"
else
    print_error "Missing PHP extensions: ${MISSING_EXTENSIONS[*]}"
    echo "Please install missing extensions:"
    echo "  Ubuntu/Debian: sudo apt-get install $(printf 'php8.2-%s ' "${MISSING_EXTENSIONS[@]}")"
    exit 1
fi

# Check Composer
print_info "Checking Composer..."
if ! command_exists composer; then
    print_error "Composer is not installed"
    echo "Please install Composer:"
    echo "  curl -sS https://getcomposer.org/installer | php"
    echo "  sudo mv composer.phar /usr/local/bin/composer"
    exit 1
fi

COMPOSER_VERSION=$(composer --version 2>&1 | grep -oP '\d+\.\d+\.\d+' | head -1)
print_success "Composer $COMPOSER_VERSION"

# Check PostgreSQL client
print_info "Checking PostgreSQL client..."
if ! command_exists psql; then
    print_warning "PostgreSQL client (psql) is not installed"
    echo "For database operations, consider installing:"
    echo "  Ubuntu/Debian: sudo apt-get install postgresql-client"
    echo "  CentOS/RHEL: sudo yum install postgresql"
else
    PSQL_VERSION=$(psql --version | grep -oP '\d+\.\d+' | head -1)
    print_success "PostgreSQL client $PSQL_VERSION"
fi

# Check Redis client
print_info "Checking Redis..."
if ! command_exists redis-cli; then
    print_warning "Redis client (redis-cli) is not installed"
    echo "For cache and queue operations, consider installing:"
    echo "  Ubuntu/Debian: sudo apt-get install redis-tools"
    echo "  CentOS/RHEL: sudo yum install redis"
else
    print_success "Redis client installed"
fi

# Check .env file
print_info "Checking environment configuration..."
APP_DIR="/var/www/html"
cd $APP_DIR

if [ ! -f .env ]; then
    print_error ".env file not found"
    echo "Please create .env file from .env.example and configure it"
    exit 1
fi
print_success ".env file exists"

# Check database connection
print_info "Testing database connection..."
if php artisan migrate:status >/dev/null 2>&1; then
    print_success "Database connection successful"
else
    print_error "Cannot connect to database"
    echo "Please verify your database configuration in .env file"
    exit 1
fi

# Check Redis connection
print_info "Testing Redis connection..."
if php -r "
    require 'vendor/autoload.php';
    try {
        \$redis = new Redis();
        \$host = getenv('REDIS_HOST') ?: '127.0.0.1';
        \$port = getenv('REDIS_PORT') ?: 6379;
        \$redis->connect(\$host, \$port);
        \$redis->ping();
        exit(0);
    } catch (Exception \$e) {
        exit(1);
    }
" 2>/dev/null; then
    print_success "Redis connection successful"
else
    print_warning "Cannot connect to Redis"
    echo "Cache and queue operations may not work properly"
fi

echo ""
print_success "All system requirements verified!"
echo ""

# ====================================
# Deployment Process
# ====================================

# Enable maintenance mode
echo "Enabling maintenance mode..."
php artisan down --retry=60

# Pull latest code
echo "Pulling latest code..."
git pull origin main

# Install dependencies
echo "Installing Composer dependencies..."
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Run migrations
echo "Running database migrations..."
php artisan migrate --force

# Clear and rebuild caches
echo "Clearing and rebuilding caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan icons:cache 2>/dev/null || true

# Restart Horizon
echo "Restarting Horizon..."
php artisan horizon:terminate

# Restart Reverb
echo "Restarting Reverb..."
supervisorctl restart laravel-reverb 2>/dev/null || true

# Restart queue workers
echo "Restarting queue workers..."
supervisorctl restart laravel-worker:* 2>/dev/null || true

# Disable maintenance mode
echo "Disabling maintenance mode..."
php artisan up

echo "=================================="
echo "Deployment completed successfully!"
echo "=================================="
