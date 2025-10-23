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

phpstan:
	@$(COMPOSER) dev:phpstan

phpcsfixer:
	@$(COMPOSER) dev:phpcsfixer

test:
	@$(COMPOSER) test:functional
