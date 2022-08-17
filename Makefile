# Run `make` (no arguments) to get a short description of what is available
# within this `Makefile`. 

help: ## shows this help
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_\-\.]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)
.PHONY: help

docker-build: ## rebuild docker containers
	docker-compose build
.PHONY: docker-build

composer-install: ## run composer installation within the docker containers (useful for local development)
	docker-compose run --rm sandbox composer install
.PHONY: composer-install

quality-assurance: tests static-analysis check-code-style ## run all quality assurance jobs

tests: ## run tests
	docker-compose run --rm sandbox vendor/bin/phpunit
.PHONY: tests

static-analysis: ## verify code type-level soundness
	docker-compose run --rm sandbox vendor/bin/psalm --no-cache
.PHONY: tests

check-code-style: ## verify coding standards are respected
	docker-compose run --rm sandbox vendor/bin/phpcs
.PHONY: tests

fix-code-style: ## auto-fix coding standard rules, where possible
	docker-compose run --rm sandbox vendor/bin/phpcbf
.PHONY: tests

