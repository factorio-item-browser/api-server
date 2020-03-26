.PHONY: help bash build-cache fix install start stop test update

help: ## Show this help.
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//'

bash: ## Run the docker container and connect to it using bash.
	docker-compose run php bash

build-cache: ## Cleans and re-nuilds the cache.
	docker-compose run php composer build-cache

install: ## Installs the dependencies of the project.
	docker-compose run php composer install

fix: ## Fixes the codestyle in the project.
	docker-compose run php composer phpcbf

start: ## Starts the images of the project.
	docker-compose up -d

stop: ## Stops the images of the project.
	docker-compose stop

test: ## Test the project.
	docker-compose run php composer test

update: ## Update the dependencies.
	docker-compose run php composer update
