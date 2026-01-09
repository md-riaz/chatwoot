# ClearLine Deployment Scripts

This directory contains comprehensive deployment scripts for the Chatwoot Laravel + SvelteKit migration project.

## Scripts Overview

### Development Scripts

#### `dev-start.sh` - Local Development Environment
- **Purpose**: Start Laravel + SvelteKit development servers locally in WSL
- **Features**:
  - Runs in current directory (no file copying)
  - Live reload for both frontend and backend
  - Hot module replacement (HMR) for SvelteKit
  - Automatic server restart on crashes
  - WebSocket server for real-time features
  - Queue processing with Horizon
  - Development database setup
  - Graceful shutdown with Ctrl+C

**Usage:**
```bash
chmod +x deployment/dev-start.sh
./deployment/dev-start.sh

# Press Ctrl+C to stop all servers
```

#### `dev-stop.sh` - Stop Development Servers
- **Purpose**: Stop all development servers started by dev-start.sh
- **Features**:
  - Stops all Laravel and SvelteKit processes
  - Cleans up PID files and logs
  - Comprehensive process cleanup

**Usage:**
```bash
chmod +x deployment/dev-stop.sh
./deployment/dev-stop.sh
```

#### `dev-status.sh` - Development Status Check
- **Purpose**: Check status of all development servers
- **Features**:
  - Shows running/stopped status of all services
  - Tests HTTP endpoints
  - Lists log file locations
  - Shows useful commands

**Usage:**
```bash
chmod +x deployment/dev-status.sh
./deployment/dev-status.sh
```

### Core Deployment Scripts

#### `deploy.sh` - Production Deployment
- **Purpose**: Production-ready deployment with Supervisor
- **Features**:
  - Creates backups before deployment
  - Uses Supervisor for process management (no nohup)
  - SSL-ready Nginx configuration
  - Comprehensive service verification
  - Automatic Reverb credential generation
  - **Note**: Frontend build is separate - run `build-frontend.sh` independently

**Usage:**
```bash
chmod +x deployment/deploy.sh
./deployment/deploy.sh

# Build frontend separately
./deployment/build-frontend.sh
```

#### `build-frontend.sh` - Frontend Build Pipeline
- **Purpose**: Builds SvelteKit frontend and integrates with Laravel
- **Features**:
  - Installs SvelteKit dependencies (prefers pnpm over npm)
  - Builds production SvelteKit SPA
  - Copies built assets to Laravel public directory
  - Sets proper file permissions
  - **Independent**: Can be run separately from backend deployment

**Usage:**
```bash
chmod +x deployment/build-frontend.sh
./deployment/build-frontend.sh
```

### Maintenance Scripts

#### `update.sh` - Zero-Downtime Updates
- **Purpose**: Safely update application with minimal downtime
- **Features**:
  - Creates pre-update backup
  - Enables maintenance mode during update
  - Updates code and dependencies
  - Runs database migrations
  - Rebuilds caches and restarts services
  - **Optional frontend build** - prompts user to build frontend
  - Comprehensive health verification

**Usage:**
```bash
chmod +x deployment/update.sh
./deployment/update.sh

# Frontend build is optional during update
# Build separately if needed: ./deployment/build-frontend.sh
```

#### `backup.sh` - Comprehensive Backup System
- **Purpose**: Creates complete application and data backups
- **Features**:
  - Backs up application code (excluding vendor/node_modules)
  - Creates database dumps (PostgreSQL/MySQL)
  - Backs up Redis data
  - Includes configuration files (Nginx, Supervisor, PHP-FPM)
  - Creates backup manifest with system info
  - Automatic cleanup of old backups (30-day retention)

**Usage:**
```bash
chmod +x deployment/backup.sh
sudo ./deployment/backup.sh
```

#### `rollback.sh` - Safe Rollback System
- **Purpose**: Rollback to previous backup with data integrity
- **Features**:
  - Lists available backups
  - Restores application code and storage
  - Restores database from backup
  - Restores Redis data
  - Rebuilds caches and restarts services
  - Creates rollback backup of current state

**Usage:**
```bash
chmod +x deployment/rollback.sh

# Rollback to latest backup
./deployment/rollback.sh

# Rollback to specific backup
./deployment/rollback.sh 20240108-143022

# Show available backups
./deployment/rollback.sh --help
```

#### `health-check.sh` - System Health Monitoring
- **Purpose**: Comprehensive health monitoring and verification
- **Features**:
  - Checks all services (Nginx, PHP-FPM, Reverb, Horizon)
  - Verifies database and Redis connections
  - Tests HTTP and WebSocket endpoints
  - Monitors disk and memory usage
  - Checks for recent errors in logs
  - Returns proper exit codes for monitoring systems

**Usage:**
```bash
chmod +x deployment/health-check.sh
./deployment/health-check.sh

# Use in cron for monitoring
*/5 * * * * /var/www/clearline/deployment/health-check.sh
```

## Configuration Files

### Nginx Configurations (`nginx/`)

#### `clearline.conf` - Production Configuration
- SSL-ready with security headers
- WebSocket proxy for Laravel Reverb
- SPA routing for SvelteKit frontend
- API routing for Laravel backend
- Static asset caching and compression
- Security restrictions for sensitive files

#### `clearline-with-ssl.conf` - SSL Configuration
- Complete SSL/TLS setup
- HTTP to HTTPS redirects
- Enhanced security headers
- HSTS and CSP policies

### Supervisor Configurations (`supervisor/`)

#### `clearline-reverb.conf` - WebSocket Server
- Manages Laravel Reverb WebSocket server
- Auto-restart on failure
- Proper logging and error handling
- Graceful shutdown handling

#### `clearline-horizon.conf` - Queue Manager
- Manages Laravel Horizon queue system
- Auto-restart and monitoring
- Comprehensive logging
- Proper signal handling

#### `clearline-worker.conf` - Queue Workers
- Additional queue worker processes
- Load balancing across multiple workers
- Failure recovery and restart policies

## Environment Setup

### Local Development Environment

**For active development in WSL Debian:**

1. **Start development servers:**
   ```bash
   ./deployment/dev-start.sh
   ```

2. **Access applications:**
   - Laravel Backend: `http://localhost:8000`
   - Laravel API: `http://localhost:8000/api`
   - SvelteKit Frontend: `http://localhost:5173`
   - WebSocket: `ws://localhost:8080`

3. **Development workflow:**
   - Edit Laravel files - changes apply immediately
   - Edit SvelteKit files - browser auto-refreshes with HMR
   - Both servers run from your current directory
   - No file copying - develop in place

4. **Manage development servers:**
   ```bash
   # Check status
   ./deployment/dev-status.sh
   
   # Stop servers
   ./deployment/dev-stop.sh
   
   # View logs
   tail -f /tmp/clearline-dev.log
   ```

### Production Environment

1. **Initial deployment:**
   ```bash
   ./deployment/deploy.sh
   
   # Build and deploy frontend separately
   ./deployment/build-frontend.sh
   ```

2. **Configure SSL certificates:**
   ```bash
   # Update nginx/clearline.conf with your SSL paths
   sudo cp deployment/nginx/clearline.conf /etc/nginx/sites-available/clearline
   sudo nginx -t && sudo systemctl reload nginx
   ```

3. **Service management:**
   ```bash
   # Check services
   sudo supervisorctl status
   
   # Restart services
   sudo supervisorctl restart clearline-reverb:*
   sudo supervisorctl restart clearline-horizon:*
   ```

## Monitoring and Maintenance

### Automated Backups

Set up daily backups with cron:
```bash
# Add to crontab
0 2 * * * /var/www/clearline/deployment/backup.sh
```

### Health Monitoring

Set up health checks:
```bash
# Add to crontab for every 5 minutes
*/5 * * * * /var/www/clearline/deployment/health-check.sh
```

### Log Rotation

Configure log rotation for application logs:
```bash
# /etc/logrotate.d/clearline
/var/www/clearline/storage/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        sudo supervisorctl restart clearline-horizon:*
    endscript
}
```

## Troubleshooting

### Common Issues

1. **Services not starting:**
   ```bash
   # Check logs
   tail -f /var/www/clearline/storage/logs/laravel.log
   tail -f /var/www/clearline/storage/logs/reverb.log
   
   # Check service status
   sudo supervisorctl status
   ```

2. **WebSocket connection issues:**
   ```bash
   # Test WebSocket port
   nc -z localhost 8080
   
   # Check Reverb configuration
   grep REVERB_ /var/www/clearline/.env
   ```

3. **Database connection issues:**
   ```bash
   # Test database connection
   sudo -u www-data php artisan migrate:status
   
   # Check database configuration
   grep DB_ /var/www/clearline/.env
   ```

4. **Frontend not loading:**
   ```bash
   # Rebuild frontend
   ./deployment/build-frontend.sh
   
   # Check if assets exist
   ls -la /var/www/clearline/public/app/
   ```

### Recovery Procedures

1. **Service recovery:**
   ```bash
   # Restart all services
   sudo supervisorctl restart all
   sudo systemctl restart nginx
   ```

2. **Application recovery:**
   ```bash
   # Run health check
   ./deployment/health-check.sh
   
   # If issues found, rollback
   ./deployment/rollback.sh
   ```

3. **Database recovery:**
   ```bash
   # Restore from latest backup
   ./deployment/rollback.sh
   
   # Or restore specific backup
   ./deployment/rollback.sh 20240108-143022
   ```

## Security Considerations

1. **File Permissions:**
   - Application files: `755` (www-data:www-data)
   - Storage directories: `775` (www-data:www-data)
   - Configuration files: `644` (root:root)

2. **Network Security:**
   - Use SSL/TLS in production
   - Configure firewall rules
   - Restrict database access
   - Use secure WebSocket connections (WSS)

3. **Application Security:**
   - Keep Laravel and dependencies updated
   - Use environment variables for secrets
   - Enable Laravel security features
   - Regular security audits

## Performance Optimization

1. **Caching:**
   - Enable OPcache for PHP
   - Use Redis for session and cache storage
   - Configure Nginx static file caching

2. **Database:**
   - Optimize PostgreSQL configuration
   - Use connection pooling
   - Regular database maintenance

3. **WebSocket:**
   - Configure Reverb for production load
   - Use Redis for WebSocket scaling
   - Monitor connection limits

## Support and Documentation

- **Laravel Documentation**: https://laravel.com/docs
- **SvelteKit Documentation**: https://kit.svelte.dev/docs
- **Laravel Reverb**: https://laravel.com/docs/reverb
- **Supervisor Documentation**: http://supervisord.org/
- **Nginx Documentation**: https://nginx.org/en/docs/

For project-specific documentation, see:
- `laravel-svelte-port/laravel/AGENTS.md` - Development guidelines
- `laravel-svelte-port/svelte-ui/llms.txt` - SvelteKit patterns
- `AGENTS.md` - Migration project overview