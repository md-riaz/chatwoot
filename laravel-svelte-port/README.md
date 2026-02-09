# Laravel-SvelteKit Migration Documentation

This directory contains the Laravel backend and SvelteKit frontend for the Chatwoot migration project, along with comprehensive documentation.

## Directory Structure

```
laravel-svelte-port/
├── laravel/                    # Laravel API backend
├── svelte-ui/                  # SvelteKit SPA frontend
├── shadcn-svelte/              # Reference: Core UI components
├── shadcn-svelte-extras/       # Reference: Extra UI components
└── docs/                       # Migration documentation (below)
```

## Documentation Files

### Quick Start
- **README.md** (this file) - Overview and navigation

### Core Documentation
1. **MIGRATION_PATTERNS.md** - Code patterns for Rails→Laravel and Vue→SvelteKit
2. **API_PARITY.md** - Maintaining Rails-Laravel API compatibility
3. **COMPONENT_LIBRARY.md** - Using shadcn-svelte components
4. **DEVELOPMENT_WORKFLOW.md** - Setup, development, and deployment
5. **COMMON_PITFALLS.md** - Known issues and how to avoid them
6. **FEATURE_EXCLUSIONS.md** - AI features exclusion details

### Project-Specific Documentation
- **laravel/AGENTS.md** - Laravel-specific patterns and conventions
- **svelte-ui/llms.txt** - Comprehensive Svelte 5 documentation
- **laravel/FOLDER_STRUCTURE.md** - Laravel architecture details

## Quick Reference

### When to Read Each File

| Task | Read This |
|------|-----------|
| Starting a new feature | MIGRATION_PATTERNS.md |
| Implementing an API endpoint | API_PARITY.md |
| Adding UI components | COMPONENT_LIBRARY.md |
| Setting up development | DEVELOPMENT_WORKFLOW.md |
| Debugging issues | COMMON_PITFALLS.md |
| Checking feature scope | FEATURE_EXCLUSIONS.md |
| Laravel-specific questions | laravel/AGENTS.md |
| Svelte 5 syntax | svelte-ui/llms.txt |

### Migration Scope

✅ **Include**: All enterprise features  
❌ **Exclude**: Copilot, Captain, AI/ML features

### Key Patterns

- **Backend**: Action → Repository → Model
- **Frontend**: Svelte 5 runes (`$state`, `$derived`, `$effect`)
- **API**: Automatic camelCase ↔ snake_case transformation
- **Pagination**: Laravel standard `paginate()` method

## Quick Start Commands

### Backend (Laravel)
```bash
cd laravel
composer install
php artisan migrate
php artisan serve
```

### Frontend (SvelteKit)
```bash
cd svelte-ui
npm install
npm run dev
```

## Completed Features

✅ Superadmin Onboarding - Feature flags, config loading  
✅ Account Seeding - Demo data generation  
✅ Laravel Pagination - Standard pagination across endpoints

## Getting Help

1. Check the relevant documentation file above
2. Review existing implementations in `laravel/` or `svelte-ui/`
3. Consult the main project AGENTS.md in the root directory
4. Ask the team if still unclear

## Contributing

When adding new features:

1. Follow patterns in MIGRATION_PATTERNS.md
2. Maintain API parity per API_PARITY.md
3. Check COMMON_PITFALLS.md before committing
4. Verify feature scope in FEATURE_EXCLUSIONS.md
5. Write tests for all new code
6. Update documentation if patterns change

## Documentation Updates

This documentation should be updated when:

- New patterns emerge
- API structures change
- Common pitfalls are discovered
- Features are completed
- Architecture decisions are made

---

**Last Updated**: February 2026  
**Status**: Active Development
