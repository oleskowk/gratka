.PHONY: up down seed shell-symfony logs tests-symfony tests-phoenix tests

tests: tests-symfony tests-phoenix

up:
	docker compose up -d

down:
	docker compose down

seed:
	docker compose exec symfony php bin/console app:seed

shell-symfony:
	docker compose exec symfony bash

logs:
	docker compose logs -f symfony

tests-symfony:
	docker compose exec symfony php vendor/bin/phpunit

tests-phoenix:
	docker compose exec phoenix env MIX_ENV=test DB_HOST=phoenix-db mix test
