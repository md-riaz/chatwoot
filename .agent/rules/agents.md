---
trigger: always_on
---

# Chatwoot Migration Project - AI Agent Guidelines

**IMPORTANT**: Terminal commands should use WSL Debian when available.

## Project Overview

Migrating from **Rails + Vue** to **Laravel + SvelteKit**

- **Current**: Rails backend, Vue frontend, PostgreSQL, ActionCable
- **Target**: Laravel API, SvelteKit SPA, PostgreSQL, Laravel Reverb
- **Location**: `laravel-svelte-port/` directory

## Quick Reference

### Migration Scope
- ✅ **Include**: All enterprise features (auth, reporting, audit, integrations, help center, automation)
- ❌ **Exclude**: Copilot, Captain, AI/ML features

### Key Patterns
- **Backend**: Action → Repository → Model (Laravel)
- **Frontend**: Svelte 5 runes (`$state`, `$derived`, `$effect`)
- **API**: camelCase ↔ snake_case (automatic transformation)
- **Pagination**: Laravel standard `paginate()` method

## Operating Principles (from CLAUDE.md)

- **Correctness over cleverness**: Boring, readable solutions
- **Smallest change that works**: Minimize blast radius
- **Leverage existing patterns**: Follow project conventions
- **Prove it works**: Validate with tests/build/lint
- **Be explicit about uncertainty**: Propose safest next step

## Essential Documentation

Read these files in `laravel-svelte-port/` as needed:

1. **MIGRATION_PATTERNS.md** - Backend/Frontend migration patterns
2. **API_PARITY.md** - Rails-Laravel API compatibility
3. **COMPONENT_LIBRARY.md** - shadcn-svelte usage
4. **DEVELOPMENT_WORKFLOW.md** - Setup and development process
5. **COMMON_PITFALLS.md** - Known issues and solutions
6. **FEATURE_EXCLUSIONS.md** - AI features exclusion details
7. **laravel/AGENTS.md** - Laravel-specific patterns
8. **svelte-ui/llms.txt** - Svelte 5 comprehensive docs

## Quick Start

```bash
# Laravel backend
cd laravel-svelte-port/laravel
composer install && php artisan migrate && php artisan serve

# SvelteKit frontend
cd laravel-svelte-port/svelte-ui
npm install && npm run dev
```

## Before Implementing Features

1. Check `MIGRATION_PATTERNS.md` for code patterns
2. Review Rails code for business logic
3. Verify API parity with `API_PARITY.md`
4. Check `COMMON_PITFALLS.md` for known issues
5. Follow Laravel/Svelte conventions from respective docs

## Completed Features

✅ **Superadmin Onboarding** - Feature flag system, config loading  
✅ **Account Seeding** - Demo data generation  
✅ **Laravel Pagination** - Standard pagination across all endpoints

See `API_PARITY.md` for implementation details.

## Getting Help

- **Laravel**: `laravel-svelte-port/laravel/AGENTS.md`
- **Svelte**: `laravel-svelte-port/svelte-ui/llms.txt`
- **Architecture**: `laravel-svelte-port/laravel/FOLDER_STRUCTURE.md`
- **Migration**: All docs in `laravel-svelte-port/`

---

**Note**: This is a high-level overview. Detailed instructions are in separate documentation files.