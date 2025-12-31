# ClearLine Laravel Development Guidelines

This file describes development, build, and behavioral guidelines for the Laravel conversion under `custom/laravel`.

## Build / Test / Lint

- **Setup**: `composer install && pnpm install`
- **Run Dev**: `pnpm dev` or `php artisan serve` (for quick local API) and `php artisan reverb:start` for websocket during integration
- **Lint PHP**: `vendor/bin/phpcs --standard=PSR12 app/` (project may use a custom ruleset)
- **Fix Styles**: `vendor/bin/phpcbf` (if using PHP CodeSniffer autofix)
- **Test PHP**: `php artisan test` (Pest / PHPUnit)
- **Test JS**: `pnpm test` or `pnpm vitest`
- **Run Project**: See `FOLDER_STRUCTURE.md` steps: `php artisan migrate`, `php artisan horizon`, `php artisan reverb:start`

## Code Style

- **PHP**: Follow PSR-12 and repository-specific rulings (use existing `phpcs.xml` if present).
- **Laravel Style**: Prefer Actions (lorisleiva/laravel-actions) for business logic, Controllers thin, Repositories for data access.
- **Events**: Use Laravel Events (`app/Events`) and Listeners (`app/Listeners`) to decouple side-effects.
- **DTOs**: Use Spatie `Data` objects under `app/Data` for typed request/response payloads.
- **Naming**: Use descriptive names and follow folder structure in `FOLDER_STRUCTURE.md`.

## Styling

- **Tailwind Only**: Use the same rules as the main project — Tailwind utilities only, no inline styles.

## General Guidelines

- Follow the Action → Repository → Model flow described in `FOLDER_STRUCTURE.md`.
- Keep controllers minimal: validate input, call an Action, return Resource.
- Prefer small, focused Actions instead of large services/builders.
- Remove dead code; avoid duplicate patterns (builders vs Actions). The codebase should consistently use Actions.
- Avoid heavy defensive programming; implement the happy path then iterate.

## Commit Messages

- Use Conventional Commits: `type(scope): subject`.

## Project-Specific Notes

- Use `app/Actions` for business behavior.
- Use `app/Repositories` for DB access and queries.
- Use `app/Data` for typed request payloads and validation.
- WebSocket: use Laravel Reverb per `FOLDER_STRUCTURE.md`.

## Recommended Local Commands

```bash
# Install dependencies
composer install
pnpm install

# Run migrations
php artisan migrate

# Run dev servers
php artisan reverb:start
php artisan horizon
pnpm dev

# Run tests
php artisan test
pnpm test
```

## Notes for Maintainers

- Before removing files, search for references across `custom/laravel` to avoid runtime errors.
- When migrating Rails behavior, prefer implementing small Actions (e.g., `SetInReplyToAction`) rather than reintroducing legacy builders.
- Keep `FOLDER_STRUCTURE.md` up to date as the canonical structure for contributors.
