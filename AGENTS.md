# Repository Guidelines

## Project Structure & Module Organization

This is the Laravel 10 REST API for Sempher's corporate-training platform; the React frontend consumes it. Application code is in `app/`: use `Models/`, `Http/Requests/`, `Http/Resources/`, and `Http/Controllers/` by responsibility. Define endpoints in `routes/api.php`; migrations, factories, and seeders belong in `database/`. Vite assets are in `resources/js` and `resources/css`; tests belong in `tests/Unit` and `tests/Feature`.

The platform persists training, CA links, prerequisites, assignments, progress, and completion history. STW is the source of truth for people, companies, products, GHEs, credentials, and administrator/supervisor roles. Do not duplicate or locally maintain those STW-owned records.

## Domain Rules and STW Integration

Scope every query, resource, and report to the authenticated user's STW company or companies. Delegate CPF/password authentication, admin authorization, and CA validation to STW, using HTTPS. Each training must link to one valid CA; mandatory training is assigned to collaborators in GHEs containing that CA. Prerequisites block availability until completed. Record a completion only after the collaborator confirms the terms checkbox, and retain its completion date for audit history.

## Build, Test, and Development Commands

- `composer install` and `npm ci` install locked dependencies (PHP 8.1+).
- `php artisan serve` starts the API; `npm run dev` starts Vite; `npm run build` creates production assets.
- `php artisan migrate --seed` updates the database and loads demo data; confirm `.env` first.
- `php artisan test` runs PHPUnit (`--filter TestName` targets one test); `vendor/bin/pint` formats PHP.

## Coding Style & Naming Conventions

Follow `.editorconfig`: UTF-8, LF, four-space indentation, no trailing whitespace, and a final newline. Use PSR-4, singular PascalCase models such as `Treinamento`, `Request`/`Resource` suffixes, and camelCase methods. Database columns remain snake_case; JSON resources expose camelCase. Run Pint before committing.

## Testing Guidelines

Tests use PHPUnit 10 and extend `Tests\\TestCase`. Name files `*Test.php` and methods `test_describes_expected_behavior`. Cover validation, serialization, company scoping, and STW/GHE rules in `tests/Feature`; reserve `tests/Unit` for isolated logic. New behavior needs focused tests; run `php artisan test` before a pull request.

## Commit & Pull Request Guidelines

Recent history uses short Portuguese summaries, occasionally with a `Fix:` prefix (for example, `Criação dos models` and `Fix: correções nas migrations`). Use concise imperative summaries, keeping each commit focused. Pull requests should explain the behavior and database/API-contract impact, link the relevant issue when available, list validation performed, and include request/response examples or screenshots when an endpoint or UI changes. Never commit `.env`, credentials, generated logs, or local build artifacts.
