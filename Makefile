.PHONY: bash test up down build

bash:
	docker compose up -d
	docker compose exec php bash || docker compose exec php sh

test:
	docker compose run --rm php vendor/bin/phpunit -c phpunit.xml

up:
	docker compose up -d

down:
	docker compose down

build:
	docker compose build
