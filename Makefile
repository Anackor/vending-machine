DEFAULT_GOAL := help

DOCKER_COMPOSE ?= docker compose
APP_SERVICE ?= app

APP_EXEC := $(DOCKER_COMPOSE) exec -T $(APP_SERVICE)
APP_EXEC_INTERACTIVE := $(DOCKER_COMPOSE) exec $(APP_SERVICE)
COMPOSER := $(APP_EXEC) composer
CONSOLE := $(APP_EXEC) php bin/console

.PHONY: help bootstrap build up down restart status install setup shell composer console lint analyse quality test test-unit test-integration coverage coverage-html phpstan deptrac rector rector-fix ecs ecs-fix mongodb-smoke

# Show the available local workflow targets.
help:
	@echo Available targets:
	@echo   bootstrap      Build, start, install dependencies, and run setup checks
	@echo   build          Build the application image
	@echo   up             Start the local Docker environment
	@echo   down           Stop the local Docker environment
	@echo   restart        Restart the local Docker environment
	@echo   status         Show Docker Compose service status
	@echo   install        Install Composer dependencies inside the app container
	@echo   setup          Run project setup checks inside the app container
	@echo   shell          Open a shell inside the app container
	@echo   composer       Run a Composer command with cmd=[command]
	@echo   console        Run a Symfony console command with cmd=[command]
	@echo   lint           Run style checks
	@echo   analyse        Run static analysis and architecture checks
	@echo   quality        Run lint and analysis together
	@echo   test           Run the full project safety-net suite
	@echo   test-unit      Run the fast unit-oriented suite
	@echo   test-integration Run the MongoDB-backed integration suite
	@echo   coverage       Run PHPUnit with text coverage output
	@echo   coverage-html  Generate the PHPUnit HTML coverage report under var/coverage/html
	@echo   phpstan        Run PHPStan
	@echo   deptrac        Run Deptrac
	@echo   rector         Run Rector in dry-run mode
	@echo   rector-fix     Run Rector and apply automatic refactors
	@echo   ecs            Run ECS in check mode
	@echo   ecs-fix        Run ECS and apply automatic fixes
	@echo   mongodb-smoke  Run the MongoDB smoke command

# Build the image, start the stack, install dependencies, and run setup checks.
bootstrap: build up install setup

# Build the application image.
build:
	$(DOCKER_COMPOSE) build $(APP_SERVICE)

# Start the local Docker environment.
up:
	$(DOCKER_COMPOSE) up -d

# Stop and remove the local Docker environment.
down:
	$(DOCKER_COMPOSE) down

# Restart the local Docker environment.
restart: down up

# Show Docker Compose service status.
status:
	$(DOCKER_COMPOSE) ps

# Install Composer dependencies inside the app container.
install:
	$(COMPOSER) install

# Run the project setup checks inside the app container.
setup:
	$(COMPOSER) run project:setup

# Open an interactive shell inside the app container.
shell:
	$(APP_EXEC_INTERACTIVE) sh

# Run a Composer command inside the app container with cmd=[command].
composer:
	$(COMPOSER) $(cmd)

# Run a Symfony console command inside the app container with cmd=[command].
console:
	$(CONSOLE) $(cmd)

# Run style checks.
lint:
	$(COMPOSER) run lint

# Run static analysis and architecture checks.
analyse:
	$(COMPOSER) run analyse

# Run lint and analysis together.
quality: lint analyse

# Run the full project safety-net suite.
test:
	$(COMPOSER) run test

# Run the fast unit-oriented suite.
test-unit:
	$(COMPOSER) run test:unit

# Run the MongoDB-backed integration suite.
test-integration:
	$(COMPOSER) run test:integration

# Run PHPUnit with text coverage output.
coverage:
	$(COMPOSER) run test:coverage

# Generate the PHPUnit HTML coverage report.
coverage-html:
	$(COMPOSER) run test:coverage:html

# Run PHPStan directly.
phpstan:
	$(COMPOSER) run analyse:phpstan

# Run Deptrac directly.
deptrac:
	$(COMPOSER) run analyse:deptrac

# Run Rector in dry-run mode directly.
rector:
	$(COMPOSER) run analyse:rector

# Run Rector and apply automatic refactors directly.
rector-fix:
	$(COMPOSER) run analyse:rector:fix

# Run ECS in check mode directly.
ecs:
	$(COMPOSER) run lint:ecs

# Run ECS and apply automatic fixes directly.
ecs-fix:
	$(COMPOSER) run lint:ecs:fix

# Run the MongoDB smoke command directly.
mongodb-smoke:
	$(CONSOLE) app:mongodb:smoke
