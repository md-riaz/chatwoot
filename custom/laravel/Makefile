# ClearLine Laravel Docker Management

.PHONY: help build up down restart logs shell test clean backup restore

# Default target
help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  %-15s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

# Development commands
build: ## Build Docker images
	docker-compose build

up: ## Start all services in development mode
	docker-compose up -d

down: ## Stop all services
	docker-compose down

restart: ## Restart all services
	docker-compose restart

logs: ## Show logs for all services
	docker-compose logs -f

logs-app: ## Show logs for app service
	docker-compose logs -f app

logs-queue: ## Show logs for queue service
	docker-compose logs -f queue

logs-db: ## Show logs for database service
	docker-compose logs -f db

shell: ## Open shell in app container
	docker-compose exec app sh

shell-db: ## Open PostgreSQL shell
	docker-compose exec db psql -U clearline -d clearline_production

# Production commands
prod-build: ## Build production images
	docker-compose -f docker-compose.yml -f docker-compose.prod.yml build

prod-up: ## Start all services in production mode
	docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d

prod-down: ## Stop production services
	docker-compose -f docker-compose.yml -f docker-compose.prod.yml down

prod-logs: ## Show production logs
	docker-compose -f docker-compose.yml -f docker-compose.prod.yml logs -f

# Application commands
migrate: ## Run database migrations
	docker-compose exec app php artisan migrate

migrate-fresh: ## Fresh migration with seeding
	docker-compose exec app php artisan migrate:fresh --seed

seed: ## Seed the database
	docker-compose exec app php artisan db:seed

cache-clear: ## Clear all caches
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear

cache-build: ## Build all caches
	docker-compose exec app php artisan config:cache
	docker-compose exec app php artisan route:cache
	docker-compose exec app php artisan view:cache

key-generate: ## Generate application key
	docker-compose exec app php artisan key:generate

storage-link: ## Create storage symbolic link
	docker-compose exec app php artisan storage:link

horizon-install: ## Install Horizon assets
	docker-compose exec app php artisan horizon:install

# Testing commands
test: ## Run tests
	docker-compose exec app php artisan test

test-coverage: ## Run tests with coverage
	docker-compose exec app php artisan test --coverage

# Maintenance commands
clean: ## Clean up Docker resources
	docker-compose down -v --remove-orphans
	docker system prune -f
	docker volume prune -f

backup: ## Backup database
	@echo "Creating database backup..."
	docker-compose exec db pg_dump -U clearline clearline_production | gzip > backup_$(shell date +%Y%m%d_%H%M%S).sql.gz
	@echo "Backup created: backup_$(shell date +%Y%m%d_%H%M%S).sql.gz"

restore: ## Restore database from backup (usage: make restore BACKUP=backup_file.sql.gz)
	@if [ -z "$(BACKUP)" ]; then echo "Usage: make restore BACKUP=backup_file.sql.gz"; exit 1; fi
	@echo "Restoring database from $(BACKUP)..."
	gunzip -c $(BACKUP) | docker-compose exec -T db psql -U clearline -d clearline_production
	@echo "Database restored from $(BACKUP)"

# Development helpers
dev-setup: ## Complete development setup
	cp .env.docker .env
	make build
	make up
	sleep 10
	make key-generate
	make migrate
	make seed
	make storage-link
	make horizon-install
	@echo "Development environment is ready!"
	@echo "Application: http://localhost:8000"
	@echo "Mailhog: http://localhost:8025"
	@echo "PgAdmin: http://localhost:8080"
	@echo "Redis Commander: http://localhost:8081"

prod-setup: ## Complete production setup
	@echo "Setting up production environment..."
	@echo "1. Copy and configure .env file"
	@echo "2. Run: make prod-build"
	@echo "3. Run: make prod-up"
	@echo "4. Configure SSL certificates"
	@echo "5. Set up monitoring and backups"

# Monitoring commands
status: ## Show service status
	docker-compose ps

stats: ## Show resource usage
	docker stats

health: ## Check application health
	curl -f http://localhost:8000/health || echo "Health check failed"

# Database commands
db-reset: ## Reset database (WARNING: destroys all data)
	docker-compose exec app php artisan migrate:fresh --seed

db-backup: ## Create database backup
	make backup

db-restore: ## Restore database (usage: make db-restore BACKUP=file.sql.gz)
	make restore BACKUP=$(BACKUP)

# Queue commands
queue-work: ## Start queue worker manually
	docker-compose exec app php artisan queue:work

queue-failed: ## Show failed jobs
	docker-compose exec app php artisan queue:failed

queue-retry: ## Retry failed jobs
	docker-compose exec app php artisan queue:retry all

queue-clear: ## Clear all queues
	docker-compose exec app php artisan queue:clear

# Horizon commands
horizon-status: ## Check Horizon status
	docker-compose exec app php artisan horizon:status

horizon-terminate: ## Terminate Horizon
	docker-compose exec app php artisan horizon:terminate

# SSL commands (for production)
ssl-generate: ## Generate self-signed SSL certificates
	mkdir -p deploy/ssl
	openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
		-keyout deploy/ssl/private.key \
		-out deploy/ssl/certificate.crt \
		-subj "/C=US/ST=State/L=City/O=Organization/CN=localhost"

# Update commands
update: ## Update application
	git pull
	make build
	make down
	make up
	make migrate
	make cache-build

# Logs and debugging
debug: ## Show debug information
	@echo "=== Docker Compose Services ==="
	docker-compose ps
	@echo ""
	@echo "=== Application Logs (last 50 lines) ==="
	docker-compose logs --tail=50 app
	@echo ""
	@echo "=== Database Status ==="
	docker-compose exec db pg_isready -U clearline -d clearline_production
	@echo ""
	@echo "=== Redis Status ==="
	docker-compose exec redis redis-cli ping