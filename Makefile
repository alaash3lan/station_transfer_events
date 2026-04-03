.PHONY: help run test migrate fresh shell docker-dev docker-prod docker-down docker-test docker-shell

.DEFAULT_GOAL := help

help: ## Show available commands
	@echo ""
	@echo "Usage: make [command]"
	@echo ""
	@echo "Local:"
	@echo "  run            Start the development server"
	@echo "  test           Run the test suite"
	@echo "  migrate        Run database migrations"
	@echo "  fresh          Drop all tables and re-run migrations"
	@echo "  shell          Open Laravel Tinker (PHP REPL)"
	@echo ""
	@echo "Docker:"
	@echo "  docker-dev     Start dev environment (with bind mounts)"
	@echo "  docker-prod    Start production environment"
	@echo "  docker-down    Stop and remove containers"
	@echo "  docker-test    Run tests inside Docker"
	@echo "  docker-shell   Open a shell inside the app container"
	@echo ""

# ── Local ──────────────────────────────────────

run:
	php artisan serve

test:
	php artisan test

migrate:
	php artisan migrate

fresh:
	php artisan migrate:fresh

shell:
	php artisan tinker

# ── Docker Dev ─────────────────────────────────

docker-dev:
	docker compose up --build

docker-down:
	docker compose down

docker-test:
	docker compose run --rm app php artisan test

docker-shell:
	docker compose exec app sh

# ── Docker Prod ────────────────────────────────

docker-prod:
	docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build
