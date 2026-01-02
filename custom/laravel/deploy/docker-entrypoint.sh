#!/bin/bash
set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}Starting ClearLine Laravel Application...${NC}"

# Function to wait for service
wait_for_service() {
    local host=$1
    local port=$2
    local service=$3
    
    echo -e "${YELLOW}Waiting for $service to be ready...${NC}"
    
    while ! nc -z $host $port; do
        echo -e "${YELLOW}$service is unavailable - sleeping${NC}"
        sleep 1
    done
    
    echo -e "${GREEN}$service is ready!${NC}"
}

# Wait for database
if [ "${DB_HOST}" != "localhost" ] && [ "${DB_HOST}" != "127.0.0.1" ]; then
    wait_for_service $DB_HOST $DB_PORT "PostgreSQL"
fi

# Wait for Redis
if [ "${REDIS_HOST}" != "localhost" ] && [ "${REDIS_HOST}" != "127.0.0.1" ]; then
    wait_for_service $REDIS_HOST $REDIS_PORT "Redis"
fi

# Set proper permissions
echo -e "${BLUE}Setting file permissions...${NC}"
chown -R www:www /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Generate application key if not set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:GENERATE_WITH_php_artisan_key:generate" ]; then
    echo -e "${YELLOW}Generating application key...${NC}"
    php artisan key:generate --force
fi

# Cache configuration
echo -e "${BLUE}Caching configuration...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
echo -e "${BLUE}Running database migrations...${NC}"
php artisan migrate --force

# Seed database if needed
if [ "$APP_ENV" = "local" ] || [ "$APP_ENV" = "development" ]; then
    echo -e "${BLUE}Seeding database...${NC}"
    php artisan db:seed --force
fi

# Create storage link
echo -e "${BLUE}Creating storage link...${NC}"
php artisan storage:link

# Install Horizon assets
echo -e "${BLUE}Installing Horizon assets...${NC}"
php artisan horizon:install

# Clear any existing caches
echo -e "${BLUE}Clearing caches...${NC}"
php artisan cache:clear
php artisan queue:clear

# Determine container role and start appropriate services
ROLE=${CONTAINER_ROLE:-app}

echo -e "${GREEN}Starting container with role: $ROLE${NC}"

case "$ROLE" in
    "app")
        echo -e "${GREEN}Starting application server...${NC}"
        exec "$@"
        ;;
    "queue")
        echo -e "${GREEN}Starting queue worker...${NC}"
        exec php artisan horizon
        ;;
    "websocket")
        echo -e "${GREEN}Starting WebSocket server...${NC}"
        exec php artisan reverb:start --host=0.0.0.0 --port=8080
        ;;
    "scheduler")
        echo -e "${GREEN}Starting scheduler...${NC}"
        exec php artisan schedule:work
        ;;
    *)
        echo -e "${RED}Unknown container role: $ROLE${NC}"
        exit 1
        ;;
esac