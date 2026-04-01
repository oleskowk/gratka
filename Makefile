tests: tests-prepare tests-symfony tests-phoenix

tests-prepare:
	docker compose exec symfony env APP_ENV=test php bin/console doctrine:database:drop --force --if-exists --no-interaction
	docker compose exec symfony env APP_ENV=test php bin/console doctrine:database:create
	docker compose exec symfony env APP_ENV=test php bin/console doctrine:migrations:migrate --no-interaction

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
	docker compose exec symfony env APP_ENV=test php vendor/bin/phpunit

tests-phoenix:
	docker compose exec phoenix env MIX_ENV=test DB_HOST=phoenix-db mix test

lint:
	docker compose exec symfony php vendor/bin/php-cs-fixer fix --dry-run --diff

lint-fix:
	docker compose exec symfony php vendor/bin/php-cs-fixer fix
