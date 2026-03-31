#!/bin/bash
set -e

composer install

echo "Waiting for database server (PostgreSQL)..."
max_attempts=15
counter=0

while ! php bin/console doctrine:database:create --if-not-exists > /dev/null 2>&1; do
    counter=$((counter+1))
    if [ $counter -eq $max_attempts ]; then
        echo "Error: Database server is not responding. Check the DB container logs."
        exit 1
    fi
    sleep 2
done

echo "Database is ready and created (if it didn't exist)."

echo "Clearing cache..."
php bin/console cache:clear

echo "Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

exec "$@"