# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Project context

This repository is the Chatwoot codebase with an ongoing migration from a **Rails + Vue** application to a **Laravel 12 API backend** (ClearLine) and a **SvelteKit SPA frontend**.

Authoritative migration and implementation rules are in `AGENTS.md`. Future agents should treat that file as the primary source of truth for migration behavior and parity guarantees.

High‑level structure:
- Root: original Chatwoot Rails app and Vue frontend (still used as the reference implementation and, in many cases, as the production app).
- `custom/laravel/`: ClearLine Laravel 12 API backend aiming for Rails API parity.
- `custom/ui/svelte-ui/`: SvelteKit SPA used as the new UI layer talking to the Laravel API.

## Commands & workflows

### Root (Rails + Vue / original Chatwoot)

All Node commands use `pnpm` (see root `package.json`).

**Frontend tests (Vitest, Vue app)**
- Run all JS/Vue tests:
  - `pnpm test`
- Watch mode for tests:
  - `pnpm test:watch`
- With coverage:
  - `pnpm test:coverage`
- Run a single/spec file (Vitest convention):
  - `pnpm test -- app/javascript/path/to/file.spec.ts`

**Linting (Vue app)**
- Lint JS/Vue sources:
  - `pnpm eslint`
- Auto‑fix lint issues:
  - `pnpm eslint:fix`

**Rails + Vue dev processes**
- Start the full dev stack via Procfiles (Rails + workers + frontend, etc.):
  - `pnpm start:dev` (Foreman + `Procfile.dev`)
  - `pnpm start:test` (test env Procfile)
  - `pnpm dev` (Overmind + `Procfile.dev`)

**Widget SDK build**
- Build the JS SDK in library mode (see `vite.config.ts`):
  - `BUILD_MODE=library pnpm build:sdk`

### Laravel backend (ClearLine, `custom/laravel/`)

All PHP tooling is managed via Composer (see `custom/laravel/composer.json`). Detailed setup and deployment instructions are in `custom/laravel/README.md` and `custom/laravel/docs/README.md`.

**Initial setup (typical local workflow)**
- From repo root:
  - `cd custom/laravel`
- Install PHP dependencies:
  - `composer install`
- Create and configure environment:
  - `cp .env.example .env`
  - edit `.env` (DB/Redis/APP_URL/CORS, etc.)
- Run migrations and seeds:
  - `php artisan migrate`
  - `php artisan db:seed`

Composer also defines a `setup` script that chains common steps:
- `composer setup`

**Running the backend**
- Basic dev server:
  - `php artisan serve`
- Full dev stack with server, queue worker, and live logs (via Composer script):
  - `composer dev`

**Queues, Horizon, Reverb (typical commands)**
- Start queue worker (simple local run):
  - `php artisan queue:work`
- Start Horizon dashboard (after `horizon:install`):
  - `php artisan horizon`
- Start Reverb WebSocket server (ports/host configured via `.env`):
  - `php artisan reverb:start`

Refer to `custom/laravel/README.md` and `custom/laravel/docs/VOICE_CHANNEL_GUIDE.md` / `WEBSOCKET_SETUP` sections for production‑grade setups.

**Tests (Pest/PHPUnit)**
From `custom/laravel/`:
- Run the full test suite with Pest:
  - `./vendor/bin/pest`
- With coverage:
  - `./vendor/bin/pest --coverage`
- Run a specific testsuite (e.g. Feature):
  - `./vendor/bin/pest --testsuite=Feature`
- Run tests in a single file:
  - `./vendor/bin/pest tests/Feature/Api/Accounts/AccountsCrudTest.php`
- Run super‑admin tests only:
  - `./vendor/bin/pest tests/Feature/SuperAdmin/`

There is also a Composer test script that clears config cache and runs `php artisan test`:
- `composer test`

**Rails parity verification helper**
- The Laravel project includes a verification helper to compare Laravel tests against Rails APIs:
  - `php verify_tests_against_rails.php`

### SvelteKit SPA (new UI, `custom/ui/svelte-ui/`)

All commands in this section run from `custom/ui/svelte-ui/` (see its `README.md` and `package.json`).

**Setup**
- `cd custom/ui/svelte-ui`
- Install dependencies:
  - `pnpm install`
- Configure environment:
  - `cp .env.example .env`
  - set `VITE_API_BASE_URL` (Laravel API URL) and `VITE_WS_URL` (Reverb WebSocket URL)

**Dev server & stories**
- Start the SvelteKit dev server:
  - `pnpm dev`
- Histoire (component/story explorer):
  - `pnpm story:dev`
  - `pnpm story:build`
  - `pnpm story:preview`

**Build**
- Production build (SPA, output to `build/`):
  - `pnpm build`
- In SPA deployments where Laravel serves the UI, built assets are typically copied into the Laravel `public` tree (see instructions in `AGENTS.md` and `custom/laravel/AGENTS.md`).

**Tests and checks**
- Run all Vitest tests:
  - `pnpm test`
- Watch mode:
  - `pnpm test:watch`
- Run a single test file:
  - `pnpm test -- src/lib/components/path/to/component.test.ts`
- Type and config checks:
  - `pnpm check`
  - `pnpm check:watch`
- Lint / format:
  - `pnpm lint`
  - `pnpm format`

## Architecture & key conventions

### Overall system

There are effectively **three major subsystems**:
1. **Rails backend + Vue frontend** (original Chatwoot app in the root Rails app and `app/javascript`), still used as the canonical reference for business logic and API behavior.
2. **ClearLine Laravel API backend** in `custom/laravel/`, targeting functional parity with the Rails APIs while following modern Laravel patterns.
3. **SvelteKit SPA frontend** in `custom/ui/svelte-ui/`, built as a standalone SPA that consumes the Laravel API.

The migration strategy is to:
- Keep the **Rails app as the behavioral spec**.
- Re‑implement APIs in Laravel with **Rails‑compatible responses** and **Laravel‑idiomatic internals**.
- Rebuild the UI in Svelte 5 while matching the UX and flows of the existing Vue app.

### Laravel backend (ClearLine)

Core ideas (see `AGENTS.md`, `custom/laravel/FOLDER_STRUCTURE.md`, and `custom/laravel/docs/README.md`):

- **Layered architecture** under `custom/laravel/app/`:
  - `Actions/`: Lorisleiva Laravel Actions encapsulate business logic (e.g., `CreateAccountAction`, `AssignConversationAction`). Controllers should primarily delegate to Actions.
  - `Data/`: Spatie Data DTOs for request/response payloads and filters, organized by domain (Account, Conversation, Message, Contact, Inbox, etc.). Prefer Data objects for type‑safe input and output.
  - `Repositories/`: Data access layer wrapping Eloquent queries; controllers/Actions should not issue complex queries directly against models.
  - `Http/Controllers/Api/V1/`: Thin API controllers mapping HTTP endpoints to Actions/Repositories and returning Resources.
  - `Http/Requests/`: Form Request classes for validation + authorization per domain.
  - `Http/Resources/`: API Resources / collections for consistent JSON formatting, including pagination responses.
  - `Events/` and `Listeners/`: Domain events (conversation created, message created, etc.) and side‑effect listeners (notifications, metrics, broadcasting).
  - `Jobs/`: Queue jobs (Horizon) for async work like sending notifications, processing messages, updating metrics.
  - `Broadcasting/`: Reverb channel classes for conversations, messages, presence, etc.
  - `Models/`: Eloquent models, including polymorphic channel models and multi‑tenant account‑scoped entities.
  - `Policies/` and middleware (`EnsureAccountAccess`, `EnsureAccountAdmin`, `EnsureSuperAdmin`): authorization layer for account‑scoped and super‑admin operations.

- **Routes** (see `custom/laravel/routes/`):
  - `api.php`: versioned APIs under `/api/v1/...` for accounts, conversations, contacts, inboxes, super admin, etc.
  - `auth.php`: authentication endpoints under `/auth/*`.
  - `web.php`: SPA fallback routes (e.g. `/app/*`) for serving the Svelte UI when integrated.

- **Super Admin API**:
  - Comprehensive platform‑level administration under `/api/v1/super_admin/*` (dashboard, accounts, users, settings, cache, audit logs, etc.) with **Rails parity** as documented in `custom/laravel/docs/API_DOCUMENTATION_COMPLETE.md`.
  - Access controlled via `EnsureSuperAdmin` middleware.

- **Queues, WebSockets, Voice**:
  - Queues and long‑running work via Laravel Horizon (`horizon` service provider and supervisor configs in `deploy/supervisor/`).
  - WebSockets via Laravel Reverb (see Reverb configuration sections in `custom/laravel/README.md`).
  - Twilio voice channel webhooks under `/api/v1/webhooks/voice/*` (call, status, conference_status), documented in `custom/laravel/README.md` and `custom/laravel/docs/VOICE_CHANNEL_GUIDE.md`.

- **Documentation set** (central references):
  - `custom/laravel/README.md`: End‑to‑end setup, onboarding flow, deployment, Reverb configuration, and test commands.
  - `custom/laravel/docs/README.md`: Index into API docs, OpenAPI specs, deployment, troubleshooting, migration, and maintenance.
  - `custom/laravel/FOLDER_STRUCTURE.md`: Detailed folder‑by‑folder architectural overview.

### SvelteKit SPA (`custom/ui/svelte-ui/`)

- **Svelte 5 + runes**: Components heavily use runes like `$state`, `$derived`, `$effect`, `$props`; see `custom/ui/svelte-ui/llms.txt` for in‑depth guidance.
- **Design system**: shadcn‑svelte style components and `bits-ui` primitives under `src/lib/components/ui/`, with Histoire stories (`*.story.svelte`) for each component.
- **Project structure** (see its `README.md`):
  - `src/routes/`: route‑level pages and layouts, configured for SPA mode (adapter‑static with `fallback: 'index.html'`).
  - `src/lib/components/`: UI and domain components (conversation card, contact card, reply box, etc.).
  - `src/lib/utils/` and `src/lib/hooks/`: shared utilities and hooks.
  - `histoire.config.ts` and `src/histoire.setup.ts`: storybook‑like documentation for components.

- **API client and case‑conversion layer** (critical from `AGENTS.md`):
  - There is an API transformation layer (e.g. `src/lib/api/transformers.ts`, `src/lib/api/client.ts`) that **automatically converts**:
    - **Outgoing requests**: camelCase → snake_case.
    - **Incoming responses**: snake_case → camelCase.
  - **Rules for agents:**
    - Always use **camelCase** in frontend TypeScript/JavaScript types and component code.
    - Do **not** manually convert between camelCase and snake_case; rely on the API client.
    - Backend APIs should expose **snake_case** fields consistent with Rails and Laravel conventions.

- **WebSocket integration**:
  - Frontend connects to Laravel Reverb using `VITE_WS_URL` and Pusher‑compatible clients (`pusher-js`).
  - In production, traffic is typically proxied through a `/ws` endpoint at the web server; see Reverb configuration details in the Laravel README.

### Rails → Laravel API parity rules (from `AGENTS.md`)

Future agents working on the Laravel API must respect these parity constraints:

- **Behavioral spec**:
  - Always inspect the Rails implementation (controllers, serializers/views) in `app/` before designing or changing a Laravel endpoint.
  - Keep field names, relationship shapes, and timestamp formats compatible with Rails responses.

- **Pagination**:
  - Use Laravel’s built‑in pagination and return the **standard Laravel pagination envelope**:
    - Keys like `data`, `current_page`, `last_page`, `per_page`, `total`, `from`, `to`, `path`, `links` must be preserved.
  - Transform the **collection** via `transform()` or Resources, but do **not** replace the top‑level pagination structure.

- **Field naming and types**:
  - Match Rails field names where they are part of the public API (e.g. `confirmed`, `locked`, `accounts_count`, etc.).
  - Map Laravel‑specific details into Rails‑compatible fields (e.g. `confirmed` from `email_verified_at`, `locked` from `custom_attributes['locked']`).
  - Use ISO‑8601 strings for timestamps (`toISOString()` on Carbon instances) to match Rails.

- **Relationships**:
  - Include Rails‑equivalent relationship structures, e.g. user `accounts` arrays with `id`, `name`, `role`, `availability`, etc., where Rails exposes them.

- **Testing**:
  - When adding/altering endpoints, write feature tests that assert JSON structure compatibility with Rails (see examples in `AGENTS.md` and the tests under `custom/laravel/tests/Feature`).

### Feature scope: AI and excluded functionality

Per `AGENTS.md`:
- **Explicitly excluded from the migration**:
  - Copilot.
  - Captain.
  - Any other AI/ML‑driven features.

Rules for agents:
- Remove or omit AI‑related behavior when porting code.
- Do **not** introduce new Copilot/Captain/AI functionality in Laravel or Svelte.
- Ensure AI feature flags remain disabled, and AI‑related tables/docs are treated as “not implemented” in the Laravel/Svelte stack.

### Where to look before making changes

When implementing or modifying behavior, prefer reading and aligning with these files before writing new code:

- **High‑level rules and migration strategy**:
  - `AGENTS.md`

- **Laravel backend**:
  - `custom/laravel/FOLDER_STRUCTURE.md` – canonical overview of how the Laravel app is organized.
  - `custom/laravel/README.md` – setup, onboarding, super‑admin features, deployment, and Reverb details.
  - `custom/laravel/docs/README.md` – index into API, deployment, migration, troubleshooting, and maintenance docs.

- **Svelte SPA**:
  - `custom/ui/svelte-ui/README.md` – SPA usage, environment, and components.
  - `custom/ui/svelte-ui/llms.txt` – Svelte 5 runes and component patterns for LLMs.

- **Original Rails/Vue app**:
  - Controllers/serializers/views under `app/` and Vue code under `app/javascript/` for the source‑of‑truth behavior and responses.

These references should be consulted instead of inventing new patterns or APIs; the goal is migration parity and consistency, not redesign.
