# ─────────────────────────────────────────────────────────────────────────────
# CONSERVICOS — Docker commands
# Usage: make <target>
# ─────────────────────────────────────────────────────────────────────────────

DC        = docker compose
APP       = $(DC) exec app
ARTISAN   = $(APP) php artisan

.PHONY: help setup up down restart build logs shell \
        migrate seed fresh tinker queue-restart \
        cache-clear test npm-dev npm-build

# ─── Default ─────────────────────────────────────────────────────────────────
help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | \
	  awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2}'

# ─── Bootstrap ───────────────────────────────────────────────────────────────
setup: ## First-time setup: copy .env, build images and start
	@[ -f .env ] || cp .env.docker .env && echo "✅ .env criado a partir de .env.docker"
	$(DC) build --no-cache
	$(MAKE) up
	@echo "\n✅ CONSERVICOS rodando em http://localhost:$$(grep APP_PORT .env | cut -d= -f2 | tr -d ' ' || echo 8000)"

# ─── Lifecycle ───────────────────────────────────────────────────────────────
up: ## Start all containers (detached)
	$(DC) up -d

down: ## Stop and remove containers
	$(DC) down

restart: ## Restart all containers
	$(DC) restart

build: ## Rebuild images (after Dockerfile or composer.json changes)
	$(DC) build

# ─── Logs ────────────────────────────────────────────────────────────────────
logs: ## Follow logs for all services
	$(DC) logs -f

logs-app: ## Follow app (PHP-FPM) logs
	$(DC) logs -f app

logs-nginx: ## Follow Nginx logs
	$(DC) logs -f nginx

logs-queue: ## Follow queue worker logs
	$(DC) logs -f queue

# ─── Shell access ─────────────────────────────────────────────────────────────
shell: ## Open bash shell in app container
	$(APP) bash

shell-mysql: ## Open MySQL CLI
	$(DC) exec mysql mysql -u root -p$$(grep DB_PASSWORD .env | cut -d= -f2) conservicos

# ─── Laravel ─────────────────────────────────────────────────────────────────
migrate: ## Run migrations
	$(ARTISAN) migrate --force

migrate-fresh: ## Drop all tables and re-run migrations + seeders
	$(ARTISAN) migrate:fresh --seed --force

seed: ## Run database seeders
	$(ARTISAN) db:seed --force

tinker: ## Open Laravel Tinker
	$(ARTISAN) tinker

cache-clear: ## Clear all Laravel caches
	$(ARTISAN) config:clear
	$(ARTISAN) route:clear
	$(ARTISAN) view:clear
	$(ARTISAN) cache:clear

queue-restart: ## Restart queue workers
	$(ARTISAN) queue:restart

# ─── Frontend ────────────────────────────────────────────────────────────────
npm-dev: ## Run Vite in dev mode (inside container)
	$(APP) npm run dev

npm-build: ## Build frontend assets
	$(APP) npm run build

# ─── Testing ─────────────────────────────────────────────────────────────────
test: ## Run PHPUnit tests
	$(APP) php artisan test
