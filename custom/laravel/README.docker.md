# ClearLine Laravel Docker Deployment

This document provides comprehensive instructions for deploying ClearLine Laravel using Docker with a complete production-ready environment.

## Quick Start

### Development Setup

1. **Clone and setup environment:**
   ```bash
   git clone <repository-url>
   cd clearline/custom/laravel
   cp .env.docker .env
   ```

2. **Start development environment:**
   ```bash
   make dev-setup
   ```

3. **Access the application:**
   - **Application**: http://localhost:8000
   - **Mailhog** (Email testing): http://localhost:8025
   - **PgAdmin** (Database admin): http://localhost:8080
   - **Redis Commander**: http://localhost:8081

### Production Setup

1. **Prepare environment:**
   ```bash
   cp .env.docker .env.production
   # Edit .env.production with your production values
   ```

2. **Deploy to production:**
   ```bash
   make prod-build
   make prod-up
   ```

## Architecture

The Docker setup includes the following services:

### Core Services

| Service | Description | Port | Health Check |
|---------|-------------|------|--------------|
| **app** | Laravel application server | 8000 | ✅ |
| **queue** | Background job processor (Horizon) | - | ✅ |
| **websocket** | WebSocket server (Reverb) | 8080 | ✅ |
| **scheduler** | Task scheduler | - | ✅ |
| **db** | PostgreSQL 16 database | 5432 | ✅ |
| **redis** | Redis cache and queue | 6379 | ✅ |

### Production Services

| Service | Description | Port | Profile |
|---------|-------------|------|---------|
| **nginx** | Reverse proxy and load balancer | 80, 443 | production |

### Development Services

| Service | Description | Port | Profile |
|---------|-------------|------|---------|
| **mailhog** | Email testing server | 8025, 1025 | development |
| **pgadmin** | PostgreSQL admin interface | 8080 | development |
| **redis-commander** | Redis admin interface | 8081 | development |

## Environment Configuration

### Environment Files

| File | Purpose | Usage |
|------|---------|-------|
| `.env.docker` | Docker development template | Copy to `.env` for development |
| `.env.production` | Production configuration | Create for production deployment |

### Key Environment Variables

```bash
# Application
APP_NAME="ClearLine"
APP_ENV=production
APP_URL=https://your-domain.com

# Database
DB_HOST=db
DB_DATABASE=clearline_production
DB_USERNAME=clearline
DB_PASSWORD=secure_password_here

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=secure_redis_password

# WebSocket
REVERB_HOST=your-domain.com
REVERB_PORT=8080
REVERB_SCHEME=https

# Ports (Docker)
APP_PORT=8000
NGINX_HTTP_PORT=80
NGINX_HTTPS_PORT=443
```

## Docker Compose Configurations

### Development (docker-compose.yml + docker-compose.override.yml)

- **Target**: Development and testing
- **Features**: Hot reloading, debugging tools, development services
- **Command**: `docker-compose up -d`

### Production (docker-compose.yml + docker-compose.prod.yml)

- **Target**: Production deployment
- **Features**: Optimized images, resource limits, production services
- **Command**: `docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d`

## Makefile Commands

The included Makefile provides convenient commands for managing the Docker environment:

### Development Commands

```bash
make dev-setup      # Complete development setup
make up             # Start development services
make down           # Stop all services
make logs           # Show all logs
make shell          # Open shell in app container
make test           # Run tests
```

### Production Commands

```bash
make prod-build     # Build production images
make prod-up        # Start production services
make prod-down      # Stop production services
make prod-logs      # Show production logs
```

### Application Commands

```bash
make migrate        # Run database migrations
make seed           # Seed database
make cache-clear    # Clear all caches
make cache-build    # Build all caches
make key-generate   # Generate application key
```

### Maintenance Commands

```bash
make backup         # Backup database
make restore        # Restore database
make clean          # Clean Docker resources
make status         # Show service status
make health         # Check application health
```

## Deployment Scenarios

### Local Development

```bash
# Quick setup
make dev-setup

# Or manual setup
cp .env.docker .env
make build
make up
make migrate
make seed
```

### Staging Environment

```bash
# Use production configuration with development services
cp .env.docker .env.staging
# Edit .env.staging with staging values

docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

### Production Environment

```bash
# Prepare production environment
cp .env.docker .env.production
# Edit .env.production with production values

# Deploy with resource limits and production optimizations
make prod-build
make prod-up

# Verify deployment
make health
make status
```

## SSL Configuration

### Development (Self-signed)

```bash
make ssl-generate
```

### Production (Let's Encrypt)

1. **Install Certbot:**
   ```bash
   sudo apt install certbot
   ```

2. **Generate certificates:**
   ```bash
   sudo certbot certonly --standalone -d your-domain.com
   ```

3. **Copy certificates:**
   ```bash
   sudo cp /etc/letsencrypt/live/your-domain.com/fullchain.pem deploy/ssl/certificate.crt
   sudo cp /etc/letsencrypt/live/your-domain.com/privkey.pem deploy/ssl/private.key
   ```

4. **Restart Nginx:**
   ```bash
   docker-compose restart nginx
   ```

## Monitoring and Logging

### Health Checks

All services include health checks:

```bash
# Check service health
make status

# Check application health
make health

# View detailed status
docker-compose ps
```

### Logging

```bash
# View all logs
make logs

# View specific service logs
make logs-app
make logs-queue
make logs-db

# Follow logs in real-time
docker-compose logs -f app
```

### Resource Monitoring

```bash
# View resource usage
make stats

# Monitor specific container
docker stats clearline-app
```

## Backup and Recovery

### Automated Backups

```bash
# Create database backup
make backup

# Restore from backup
make restore BACKUP=backup_20250102_120000.sql.gz
```

### Manual Backup

```bash
# Database backup
docker-compose exec db pg_dump -U clearline clearline_production > backup.sql

# Application files backup
docker cp clearline-app:/var/www/html/storage ./storage-backup
```

## Scaling and Performance

### Horizontal Scaling

Scale individual services:

```bash
# Scale queue workers
docker-compose up -d --scale queue=3

# Scale application servers (with load balancer)
docker-compose up -d --scale app=2
```

### Resource Limits

Production configuration includes resource limits:

```yaml
deploy:
  resources:
    limits:
      memory: 1G
      cpus: '1.0'
    reservations:
      memory: 512M
      cpus: '0.5'
```

### Performance Optimization

1. **Database optimization** (included in postgres init)
2. **Redis memory management** (configured in docker-compose)
3. **PHP OPcache** (enabled in PHP configuration)
4. **Nginx caching** (configured for static assets)

## Security

### Container Security

- **Non-root user**: Application runs as `www` user
- **Read-only filesystem**: Where possible
- **Security headers**: Configured in Nginx
- **Network isolation**: Services communicate via internal network

### Environment Security

```bash
# Secure environment variables
chmod 600 .env.production

# Use Docker secrets for sensitive data (Docker Swarm)
echo "secure_password" | docker secret create db_password -
```

### SSL/TLS

- **HTTPS enforcement** in production
- **Strong cipher suites** configured
- **HSTS headers** enabled

## Troubleshooting

### Common Issues

1. **Port conflicts:**
   ```bash
   # Change ports in .env file
   APP_PORT=8001
   NGINX_HTTP_PORT=8080
   ```

2. **Permission issues:**
   ```bash
   # Fix storage permissions
   docker-compose exec app chown -R www:www storage bootstrap/cache
   ```

3. **Database connection:**
   ```bash
   # Check database status
   docker-compose exec db pg_isready -U clearline
   
   # View database logs
   make logs-db
   ```

4. **Memory issues:**
   ```bash
   # Increase memory limits in docker-compose.prod.yml
   deploy:
     resources:
       limits:
         memory: 2G
   ```

### Debug Mode

```bash
# Enable debug mode (development only)
docker-compose exec app php artisan tinker
>>> config(['app.debug' => true]);

# View debug information
make debug
```

### Log Analysis

```bash
# Check application errors
docker-compose exec app tail -f storage/logs/laravel.log

# Check web server errors
docker-compose exec nginx tail -f /var/log/nginx/error.log

# Check system logs
docker-compose logs --tail=100 app
```

## Maintenance

### Regular Maintenance

```bash
# Weekly maintenance
make backup
make cache-clear
make cache-build

# Monthly maintenance
make clean
docker system prune -f
```

### Updates

```bash
# Update application
make update

# Update Docker images
docker-compose pull
make build
make restart
```

### Database Maintenance

```bash
# Optimize database
docker-compose exec db psql -U clearline -d clearline_production -c "VACUUM ANALYZE;"

# Check database size
docker-compose exec db psql -U clearline -d clearline_production -c "SELECT pg_size_pretty(pg_database_size('clearline_production'));"
```

## Support

### Getting Help

1. **Check logs**: `make logs` or `make debug`
2. **Verify configuration**: Check `.env` file
3. **Test connectivity**: `make health`
4. **Review documentation**: This file and deployment guides

### Useful Commands

```bash
# Complete system status
make status && make health && make stats

# Emergency restart
make down && make up

# Clean restart
make clean && make build && make up
```

---

**Last Updated:** 2025-01-02  
**Docker Version:** 24.0+  
**Docker Compose Version:** 2.0+