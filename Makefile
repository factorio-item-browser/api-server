.PHONY: help bash build-cache install start stop test update

help: ## Show this help.
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//'

bash: ## Run the docker container and connect to it using bash.
	docker-compose run php bash

build-cache: ## Cleans and re-nuilds the cache.
	docker-compose run php composer build-cache

install:
	docker-compose run php install

start:
	docker-compose up -d

stop:
	docker-compose stop

test: ## Test the project.
	docker-compose run php composer test

update: ## Update the dependencies.
	docker-compose run php composer update
