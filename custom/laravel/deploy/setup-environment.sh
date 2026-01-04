#!/bin/bash
# Environment Setup Script for ClearLine Laravel Application
# This script prepares a fresh server for deploying the application

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo "==========================================="
echo "ClearLine Environment Setup"
echo "==========================================="
echo ""

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
    echo -e "${BLUE}ℹ $1${NC}"
}

# Detect OS
detect_os() {
    if [ -f /etc/os-release ]; then
        . /etc/os-release
        OS=$ID
        OS_VERSION=$VERSION_ID
    else
        print_error "Cannot detect OS"
        exit 1
    fi
}

# Check if script is run as root
if [ "$EUID" -ne 0 ]; then 
    print_warning "This script should be run as root or with sudo"
    echo "Some installations may fail without proper permissions"
    read -p "Continue anyway? (y/N) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

detect_os
print_info "Detected OS: $OS $OS_VERSION"
echo ""

# ====================================
# PHP Installation
# ====================================
echo "1. Installing PHP 8.2 and extensions..."
echo "========================================"

install_php_ubuntu() {
    print_info "Adding PHP repository..."
    apt-get update
    apt-get install -y software-properties-common
    add-apt-repository ppa:ondrej/php -y
    apt-get update
    
    print_info "Installing PHP 8.2 and extensions..."
    apt-get install -y \
        php8.2-cli \
        php8.2-fpm \
        php8.2-pgsql \
        php8.2-redis \
        php8.2-mbstring \
        php8.2-xml \
        php8.2-curl \
        php8.2-zip \
        php8.2-bcmath \
        php8.2-intl \
        php8.2-gd \
        php8.2-opcache \
        php8.2-soap \
        php8.2-tokenizer
    
    print_success "PHP 8.2 installed successfully"
}

install_php_centos() {
    print_info "Adding EPEL and Remi repositories..."
    yum install -y epel-release
    yum install -y https://rpms.remirepo.net/enterprise/remi-release-$(rpm -E %{rhel}).rpm
    yum module reset php -y
    yum module enable php:remi-8.2 -y
    
    print_info "Installing PHP 8.2 and extensions..."
    yum install -y \
        php \
        php-cli \
        php-fpm \
        php-pgsql \
        php-redis \
        php-mbstring \
        php-xml \
        php-curl \
        php-zip \
        php-bcmath \
        php-intl \
        php-gd \
        php-opcache \
        php-soap \
        php-json
    
    print_success "PHP 8.2 installed successfully"
}

if command -v php >/dev/null 2>&1; then
    PHP_VERSION=$(php -r 'echo PHP_VERSION;')
    print_info "PHP $PHP_VERSION is already installed"
    
    if [[ "$PHP_VERSION" < "8.2" ]]; then
        print_warning "PHP version is below 8.2"
        read -p "Upgrade to PHP 8.2? (y/N) " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            if [ "$OS" = "ubuntu" ] || [ "$OS" = "debian" ]; then
                install_php_ubuntu
            elif [ "$OS" = "centos" ] || [ "$OS" = "rhel" ]; then
                install_php_centos
            fi
        fi
    else
        print_success "PHP version meets requirements (>= 8.2)"
    fi
else
    if [ "$OS" = "ubuntu" ] || [ "$OS" = "debian" ]; then
        install_php_ubuntu
    elif [ "$OS" = "centos" ] || [ "$OS" = "rhel" ]; then
        install_php_centos
    else
        print_error "Unsupported OS for automatic PHP installation"
        echo "Please install PHP 8.2+ manually"
        exit 1
    fi
fi

echo ""

# ====================================
# Composer Installation
# ====================================
echo "2. Installing Composer..."
echo "=========================="

if command -v composer >/dev/null 2>&1; then
    print_success "Composer is already installed"
else
    print_info "Downloading Composer installer..."
    cd /tmp
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    
    print_info "Installing Composer..."
    php composer-setup.php --quiet
    mv composer.phar /usr/local/bin/composer
    rm composer-setup.php
    
    print_success "Composer installed successfully"
fi

COMPOSER_VERSION=$(composer --version 2>&1 | grep -oP '\d+\.\d+\.\d+' | head -1)
print_info "Composer version: $COMPOSER_VERSION"
echo ""

# ====================================
# PostgreSQL Installation
# ====================================
echo "3. Installing PostgreSQL..."
echo "============================"

install_postgresql_ubuntu() {
    print_info "Adding PostgreSQL repository..."
    sh -c 'echo "deb http://apt.postgresql.org/pub/repos/apt $(lsb_release -cs)-pgdg main" > /etc/apt/sources.list.d/pgdg.list'
    wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | apt-key add -
    apt-get update
    
    print_info "Installing PostgreSQL 16..."
    apt-get install -y postgresql-16 postgresql-client-16
    
    print_success "PostgreSQL 16 installed successfully"
}

install_postgresql_centos() {
    print_info "Adding PostgreSQL repository..."
    yum install -y https://download.postgresql.org/pub/repos/yum/reporpms/EL-$(rpm -E %{rhel})-x86_64/pgdg-redhat-repo-latest.noarch.rpm
    
    print_info "Installing PostgreSQL 16..."
    yum install -y postgresql16 postgresql16-server
    
    print_info "Initializing PostgreSQL database..."
    /usr/pgsql-16/bin/postgresql-16-setup initdb
    systemctl enable postgresql-16
    systemctl start postgresql-16
    
    print_success "PostgreSQL 16 installed successfully"
}

if command -v psql >/dev/null 2>&1; then
    print_success "PostgreSQL client is already installed"
    PSQL_VERSION=$(psql --version | grep -oP '\d+\.\d+' | head -1)
    print_info "PostgreSQL version: $PSQL_VERSION"
else
    read -p "Install PostgreSQL 16? (y/N) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        if [ "$OS" = "ubuntu" ] || [ "$OS" = "debian" ]; then
            install_postgresql_ubuntu
        elif [ "$OS" = "centos" ] || [ "$OS" = "rhel" ]; then
            install_postgresql_centos
        else
            print_warning "Unsupported OS for automatic PostgreSQL installation"
            echo "Please install PostgreSQL 14+ manually"
        fi
    else
        print_warning "Skipping PostgreSQL installation"
        echo "Make sure PostgreSQL 14+ is installed and accessible"
    fi
fi

echo ""

# ====================================
# Redis Installation
# ====================================
echo "4. Installing Redis..."
echo "======================"

install_redis_ubuntu() {
    print_info "Installing Redis..."
    apt-get install -y redis-server
    
    systemctl enable redis-server
    systemctl start redis-server
    
    print_success "Redis installed successfully"
}

install_redis_centos() {
    print_info "Installing Redis..."
    yum install -y redis
    
    systemctl enable redis
    systemctl start redis
    
    print_success "Redis installed successfully"
}

if command -v redis-cli >/dev/null 2>&1; then
    print_success "Redis is already installed"
    REDIS_VERSION=$(redis-cli --version | grep -oP '\d+\.\d+\.\d+' | head -1)
    print_info "Redis version: $REDIS_VERSION"
else
    read -p "Install Redis? (y/N) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        if [ "$OS" = "ubuntu" ] || [ "$OS" = "debian" ]; then
            install_redis_ubuntu
        elif [ "$OS" = "centos" ] || [ "$OS" = "rhel" ]; then
            install_redis_centos
        else
            print_warning "Unsupported OS for automatic Redis installation"
            echo "Please install Redis 6+ manually"
        fi
    else
        print_warning "Skipping Redis installation"
        echo "Redis is required for cache and queue operations"
    fi
fi

echo ""

# ====================================
# Additional Dependencies
# ====================================
echo "5. Installing additional dependencies..."
echo "========================================="

if [ "$OS" = "ubuntu" ] || [ "$OS" = "debian" ]; then
    print_info "Installing system dependencies..."
    apt-get install -y \
        git \
        curl \
        wget \
        unzip \
        supervisor \
        nginx
    
    print_success "System dependencies installed"
elif [ "$OS" = "centos" ] || [ "$OS" = "rhel" ]; then
    print_info "Installing system dependencies..."
    yum install -y \
        git \
        curl \
        wget \
        unzip \
        supervisor \
        nginx
    
    print_success "System dependencies installed"
fi

echo ""

# ====================================
# Configuration Guidance
# ====================================
echo "6. Configuration Steps..."
echo "=========================="
echo ""
print_info "Next steps to complete setup:"
echo ""
echo "1. Create PostgreSQL database and user:"
echo "   sudo -u postgres psql"
echo "   CREATE DATABASE clearline_production;"
echo "   CREATE USER clearline WITH PASSWORD 'CHANGE_THIS_PASSWORD';"
echo "   GRANT ALL PRIVILEGES ON DATABASE clearline_production TO clearline;"
echo "   \\q"
echo ""
echo "2. Configure Redis (optional, for remote access):"
echo "   Edit /etc/redis/redis.conf"
echo "   Set bind to your IP or 0.0.0.0"
echo "   Set requirepass for authentication"
echo "   sudo systemctl restart redis"
echo ""
echo "3. Clone your application:"
echo "   cd /var/www"
echo "   git clone <your-repository-url> html"
echo "   cd html/custom/laravel"
echo ""
echo "4. Configure environment:"
echo "   cp .env.example .env"
echo "   Edit .env with your database, Redis, and app settings"
echo "   php artisan key:generate"
echo ""
echo "5. Install application dependencies:"
echo "   composer install --no-dev"
echo ""
echo "6. Run migrations:"
echo "   php artisan migrate"
echo "   php artisan db:seed"
echo ""
echo "7. Configure web server (Nginx/Apache)"
echo ""
echo "8. Set up supervisor for queue workers and Reverb"
echo ""

echo ""
echo "==========================================="
print_success "Environment setup completed!"
echo "==========================================="
print_info "System is ready for ClearLine deployment"
echo ""
