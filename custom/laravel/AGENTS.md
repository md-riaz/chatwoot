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


## SPA Asset Placement & Serving

- Build the SvelteKit SPA in `custom/ui/sveltekit-app` using `npm run build`.
- Copy the build output (usually `build/` or `.svelte-kit/output/`) to `custom/laravel/public/app/`.
- All static assets (JS, CSS, images) must be inside `public/app/` so Laravel can serve them directly.

## Routing Setup

- All `/api/*` and `/auth/*` endpoints are handled by Laravel (see `routes/api.php` and `routes/auth.php`).
- All `/app/*` requests (including `/app`, `/app/super_admin`, etc.) are routed by Laravel to serve the SPA entrypoint (`public/app/index.html`).
- Unknown routes under `/app/*` always fall back to the SPA entrypoint.
- See `routes/web.php` for example configuration:

```php
Route::get('/app/{any}', function () {
	 return response()->file(public_path('app/index.html'));
})->where('any', '.*');
Route::get('/app', function () {
	 return response()->file(public_path('app/index.html'));
});
```

## Local Development Workflow

1. Install dependencies:
	```bash
	cd custom/laravel
	composer install
	pnpm install
	```
2. Run Laravel backend:
	```bash
	php artisan serve
	# Default: http://localhost:8000
	```
3. Run SvelteKit frontend:
	```bash
	cd custom/ui/sveltekit-app
	npm run dev
	# Default: http://localhost:5173
	```
4. Proxy API requests from SvelteKit to Laravel (in `vite.config.js`):
	```js
	export default {
	  server: {
		 proxy: {
			'/api': 'http://localhost:8000',
			'/auth': 'http://localhost:8000',
		 }
	  }
	}
	```

## Production Deployment

1. Build SvelteKit:
	```bash
	cd custom/ui/sveltekit-app
	npm run build
	```
2. Copy build output to Laravel:
	```bash
	cp -r build/* ../../laravel/public/app/
	```
3. Deploy Laravel as usual (web server points to `public/`).
	- All `/app/*` requests are routed to SPA entrypoint (`index.html`).
	- All `/api/*` and `/auth/*` requests are handled by Laravel backend.

## Error Handling

- Backend routes (not `/app/*`):
  - Return JSON 404 for API/Auth.
  - Return backend error page for other routes.
- SPA routes (`/app/*`):
  - Always serve `public/app/index.html` for any subroute (SPA handles client-side routing).
