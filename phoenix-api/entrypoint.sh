#!/bin/bash

# Wait for Postgres
echo "Waiting for PostgreSQL to be ready..."
while ! nc -z phoenix-db 5432; do
  sleep 0.1
done
echo "PostgreSQL started"

set -e

mix deps.get

mix ecto.create 2>/dev/null || true

mix ecto.migrate

exec mix phx.server
