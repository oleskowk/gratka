## Architektura

Ten projekt składa się z dwóch oddzielnych aplikacji z własnymi bazami danych:

- **Symfony App** (port 8000): Główna aplikacja internetowa
  - Baza danych: `symfony-db` (PostgreSQL, port 5432)
  - Nazwa bazy danych: `symfony_app`

- **Phoenix API** (port 4000): Mikroserwis REST API
  - Baza danych: `phoenix-db` (PostgreSQL, port 5433)
  - Nazwa bazy danych: `phoenix_api`

## Szybki start

1. Skonfiguruj środowisko (skopiuj szablon i wygeneruj klucze):
   ```bash
   cp .env.dist .env
   ```

### Konfiguracja klucza szyfrującego

W głównym pliku `.env` musisz zdefiniować zmienną `SYMFONY_PHOENIX_ENCRYPTION_KEY`. Klucz ten musi mieć dokładnie **32 bajty** (po odkodowaniu z base64).

Aby wygenerować nowy, bezpieczny klucz, użyj następującej komendy:

```bash
docker-compose exec symfony php -r "echo base64_encode(random_bytes(32)) . PHP_EOL;"
```

Następnie skopiuj wygenerowany ciąg i wklej go do pliku `.env`:

```env
SYMFONY_PHOENIX_ENCRYPTION_KEY=TwojWygenerowanyKlucz...
```

2. Uruchom kontenery i zainicjuj bazy danych:
   ```bash
   docker-compose up -d

   # Konfiguracja Symfony
   docker-compose exec symfony php bin/console app:seed

   # Konfiguracja Phoenix
   docker-compose exec phoenix mix ecto.migrate
   docker-compose exec phoenix mix run priv/repo/seeds.exs
   ```

Dostęp do aplikacji:
- Symfony App: http://localhost:8000
- Phoenix API: http://localhost:4000

## Komendy Symfony

### Migracja bazy danych
```bash
docker-compose exec symfony php bin/console doctrine:migrations:migrate --no-interaction
```

### Ponowne tworzenie bazy danych
```bash
docker-compose exec symfony php bin/console doctrine:schema:drop --force --full-database
docker-compose exec symfony php bin/console doctrine:migrations:migrate --no-interaction
docker-compose exec symfony php bin/console app:seed
```

### Czyszczenie pamięci podręcznej (Cache)
```bash
docker-compose exec symfony php bin/console cache:clear
```

### Restart
```bash
docker-compose restart symfony
```

### Uruchamianie testów
```bash
docker-compose exec symfony php vendor/bin/phpunit
# Lub przez make:
make tests-symfony
```

### Analiza statyczna i linter
```bash
# Sprawdzenie błędów stylu (dry-run)
make lint
# Automatyczna poprawa błędów stylu
make lint-fix
# Analiza statyczna
make phpstan
```

## Komendy Phoenix

### Migracja bazy danych
```bash
docker-compose exec phoenix mix ecto.migrate
```

### Seedowanie bazy danych
```bash
docker-compose exec phoenix mix run priv/repo/seeds.exs
```

### Ponowne tworzenie bazy danych
```bash
docker-compose exec phoenix mix ecto.reset
docker-compose exec phoenix mix run priv/repo/seeds.exs
```

### Restart
```bash
docker-compose restart phoenix
```

### Uruchamianie testów
```bash
docker-compose exec phoenix env MIX_ENV=test DB_HOST=phoenix-db mix test
# Lub przez make:
make tests-phoenix
```

## 📦 Git & Commits

- **Atomic Commits**: Twórz małe, odizolowane commity skupione na jednej, konkretnej zmianie.
- **Descriptive Messages**: Używaj jasnych, opisowych wiadomości commitów w języku angielskim (np. "Fix: Resolve N+1 query in PhotoRepository").

## 🧪 Wszystkie testy

Możesz uruchomić testy dla obu aplikacji jednocześnie za pomocą:
```bash
make tests
```
