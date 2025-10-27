ENV_FILE = .env

-include $(ENV_FILE)


# —— Setup ————————————————————————————————————————————————————————————————————
PROJECT        = supply
DOCKER_COMPOSE = docker compose
PHP = $(DOCKER_COMPOSE) run --rm php
COMPOSER = $(PHP) composer run

start: docker-up ## Start the project
stop: docker-down ## Stop the project
restart: docker-restart ## Restart the project
init: docker-build docker-up

docker-build:
	@$(DOCKER_COMPOSE) build

docker-up: ## Start the docker hub in detached mode (no logs)
	@$(DOCKER_COMPOSE) up --detach

docker-down: ## Stop the docker hub
	@$(DOCKER_COMPOSE) down --remove-orphans

docker-restart: docker-down docker-up  ## STOP AND RESTART

phpstan: ## Run PHPSTAN in DEV MODE
	@$(COMPOSER) dev:phpstan

phpcsfixer: ## Run PHPCSFIXER TO FIX Code
	@$(COMPOSER) dev:phpcsfixer

test: ## Run Functionnal testing
	@$(COMPOSER) test:functional

help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9\./_-]+:.*?##.*$$)|(^##)' Makefile | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'
