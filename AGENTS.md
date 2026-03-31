This document serves as a set of rules and best practices for AI agents working on this project. All AI interventions must adhere to these guidelines to ensure consistency, maintainability.

## 🧱 Architecture Guidelines

- Do NOT introduce new frameworks or libraries without justification.
- Prefer consistency over innovation.
- Reuse existing services/components whenever possible.

## 🚫 Constraints

- Do NOT refactor unrelated code.
- Do NOT introduce breaking changes without explicit instruction.
- Do NOT change formatting or style outside the scope of the task.
- Do NOT add dependencies without checking existing ones.

## 📦 Monorepo Structure

- `/symfony-app` – Symfony application (PHP)
- `/phoenix-api` – Phoenix application (Elixir)
- Each subproject MAY define its own `AGENTS.md` with more specific rules.
- When working on a task, ALWAYS identify the target application first.
- NEVER mix conventions between PHP and Elixir projects.

## 🛠 Infrastructure & Environment

- **Docker-First Approach**: All applications, tests, and CLI commands MUST be executed inside Docker containers. NEVER run commands directly on the host machine.
- **Service Management**: Use `docker-compose exec [service] [command]` for running migrations, tests, or seeds.

## 🔤 Language & Coding Standards

- **Code & Comments**: All code (variable names, class names, functions, etc.) and comments MUST be in **English**.
- **Documentation**: Technical documentation within the code must be in English. General READMEs or user-facing docs can follow the user's preference (Polish/English), but English is the default for technical internals.


## 🔄 Development Workflow

When implementing changes:

1. Understand the context and target application.
2. Check for existing patterns in the codebase.
3. Implement the solution following local conventions.
4. Add or update tests.
5. Run tests inside Docker.
6. Ensure no regressions.


## ✅ Quality Assurance & Testing

- **Test-Driven Thinking**: Always consider how a change will be tested. Prefer writing tests alongside the implementation.
- **Test Execution**: Run tests inside Docker before finalizing any task.
  - Symfony: `docker-compose exec symfony php vendor/bin/phpunit`
  - Phoenix: `docker-compose exec phoenix env MIX_ENV=test DB_HOST=phoenix-db mix test`
- **Test Integrity**: NEVER modify existing tests to match a faulty implementation. If a test fails, prioritize fixing the code to meet the original test's expectations. Only update tests if the feature's requirements have explicitly changed.
- **Error Handling**: Use structured error handling. Avoid generic "catch-all" blocks without logging or specific recovery strategies.


## 🚀 Performance & Security

- **Database Optimization**: Avoid N+1 queries. Use Ecto's `preload` or Doctrine's `JOIN FETCH` when fetching related data.
- **Sensitive Data**: Never hardcode secrets. Use environment variables defined in `.env` or `docker-compose.yml`.
- **Validation**: Always validate input at the edges of the system (API controllers, CLI input).
